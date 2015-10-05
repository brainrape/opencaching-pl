<?php
namespace lib\Objects\PowerTrail;

use \lib\Database\DataBaseSingleton;
use \lib\Objects\Coordinates\Coordinates;
use lib\Objects\GeoCache\ Collection;
use lib\Objects\GeoCache\GeoCache;

class PowerTrail
{

    const TYPE_GEODRAW = 1;
    const TYPE_TOURING = 2;
    const TYPE_NATURE = 3;
    const TYPE_TEMATIC = 4;

    private $id;
    private $name;
    private $image;
    private $type;
    private $centerCoordinates;
    private $status;
    /* @var $dateCreated \DateTime */
    private $dateCreated;
    private $cacheCount;
    private $description;
    private $perccentRequired;
    private $conquestedCount;
    private $points;
    /* @var $geocaches \lib\Objects\GeoCache\Collection */
    private $geocaches;
    private $owners = false;

    private $powerTrailConfiguration;
    
    public function __construct(array $params)
    {
        if (isset($params['id'])) {
            $this->id = (int) $params['id'];
            
            if(isset($params['fieldsStr']))
                $this->loadDataFromDb($params['fieldsStr']);
            else
                $this->loadDataFromDb();
            
        } elseif (isset($params['dbRow'])) {
            $this->setFieldsByUsedDbRow($params['dbRow']);
        } else {
            $this->centerCoordinates = new Coordinates();
        }
        $this->geocaches = new Collection();
    }

    private function loadDataFromDb($fields=null)
    {
        $db = \lib\Database\DataBaseSingleton::Instance();

        if(is_null($fields)){
            //default select all fields
            $fields = "*"; 
        }

        $ptq = "SELECT $fields FROM `PowerTrail` WHERE `id` = :1 LIMIT 1";
        $db->multiVariableQuery($ptq, $this->id);
        $this->setFieldsByUsedDbRow($db->dbResultFetch());
    }

    private function setFieldsByUsedDbRow(array $dbRow)
    {
        
        foreach($dbRow as $key=>$val){
            switch($key){
                case 'id':         $this->id = (int) $val;     break;
                case 'name':     $this->name = $val;            break;
                case 'image':     $this->image = $val;         break;
                case 'type':     $this->type = (int) $val;     break;
                case 'status':     $this->status = (int) $val; break;
                case 'dateCreated':
                    $this->dateCreated = new \DateTime($val); break;
                case 'cacheCount':     
                    $this->cacheCount = (int) $val;         break;
                case 'description':     
                    $this->description = $val;                 break;
                case 'perccentRequired':
                        $this->perccentRequired = $val;     break;
                case 'conquestedCount':
                        $this->conquestedCount = $val;         break;
                case 'points':
                        $this->points = $val;                 break;
                        
                case 'centerLatitude':
                case 'centerLongitude':
                    //cords are handled below...
                    break;
                default:
                    error_log(__METHOD__.": Unknown column: $key");
            }
        }
        
        // and the coordinates..
        if(isset($dbRow['centerLatitude'], $dbRow['centerLongitude'])){ 
            $this->centerCoordinates = new Coordinates();
            $this->centerCoordinates
                        ->setLatitude($dbRow['centerLatitude'])
                        ->setLongitude($dbRow['centerLongitude']);
        }

    }

    public static function CheckForPowerTrailByCache($cacheId)
    {
        $queryPt = 'SELECT `id`, `name`, `image`, `type` FROM `PowerTrail` WHERE `id` IN ( SELECT `PowerTrailId` FROM `powerTrail_caches` WHERE `cacheId` =:1 ) AND `status` = 1 ';
        $db = DataBaseSingleton::Instance();
        $db->multiVariableQuery($queryPt, $cacheId);

        return $db->dbResultFetchAll();
    }

    public static function GetPowerTrailIconsByType($typeId)
    {
        $imgPath = '/tpl/stdstyle/images/blue/';
        $icon = '';
        switch ($typeId) {
            case self::TYPE_GEODRAW:
                $icon = 'footprintRed.png';
                break;
            case self::TYPE_TOURING:
                $icon = 'footprintBlue.png';
                break;
            case self::TYPE_NATURE:
                $icon = 'footprintGreen.png';
                break;
            case self::TYPE_TEMATIC:
                $icon = 'footprintYellow.png';
                break;
        }
        return $imgPath . $icon;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getFootIcon()
    {
        return self::GetPowerTrailIconsByType($this->type);
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getPowerTrailUrl()
    {
        $url = '/powerTrail.php?ptAction=showSerie&ptrail=';
        return $url . $this->id;
    }

    /**
     * @return  Collection
     */
    public function getGeocaches()
    {
        if(!$this->geocaches->isReady()){
            $db = DataBaseSingleton::Instance();
            $query = 'SELECT powerTrail_caches.isFinal, caches . * , user.username FROM  `caches` , user, powerTrail_caches WHERE cache_id IN ( SELECT  `cacheId` FROM  `powerTrail_caches` WHERE  `PowerTrailId` =:1) AND user.user_id = caches.user_id AND powerTrail_caches.cacheId = caches.cache_id ORDER BY caches.name';
            $db->multiVariableQuery($query, $this->id);
            $geoCachesDbResult = $db->dbResultFetchAll();
            $geocachesIdArray = array();
            foreach ($geoCachesDbResult as $geoCacheDbRow) {
                $geocache = new GeoCache();
                $geocache->loadFromRow($geoCacheDbRow)->setIsPowerTrailPart(true);
                $geocache->setPowerTrail($this);
                if($geoCacheDbRow['isFinal'] == 1){
                    $geocache->setIsPowerTrailFinalGeocache(true);
                }
                $this->geocaches[] = $geocache;
                $geocachesIdArray[] = $geocache->getCacheId();
            }
            $this->geocaches->setIsReady(true);
            $this->geocaches->setGeocachesIdArray($geocachesIdArray);
        }
        return $this->geocaches;
    }

    private function loadPtOwners(){
        $query = 'SELECT `userId`, `privileages`, username FROM `PowerTrail_owners`, user WHERE `PowerTrailId` = :1 AND PowerTrail_owners.userId = user.user_id';
        $db = \lib\Database\DataBaseSingleton::Instance();
        $db->multiVariableQuery($query, $this->id);
        $ownerDb = $db->dbResultFetchAll();
        foreach ($ownerDb as $user) {
            $owner = new Owner($user);
            $owner->setPrivileages($user['privileages']);
            $this->owners[] = $owner;
        }
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getConquestedCount()
    {
        return $this->conquestedCount;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getCacheCount()
    {
        return $this->cacheCount;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return mixed
     */
    public function getPerccentRequired()
    {
        return $this->perccentRequired;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return Coordinates
     */
    public function getCenterCoordinates()
    {
        return $this->centerCoordinates;
    }

    /**
     * @param mixed $powerTrailConfiguration
     * @return PowerTrail
     */
    public function setPowerTrailConfiguration($powerTrailConfiguration)
    {
        $this->powerTrailConfiguration = $powerTrailConfiguration;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwners()
    {
        if(!$this->owners){
            $this->loadPtOwners();
        }
        return $this->owners;
    }

    /**
     * check if specified user is owner of the powerTrail
     * @param integer $userId
     * @return bool
     */
    public function isUserOwner($userId)
    {
        $owners = $this->getOwners();
        foreach ($owners as $owner) {
            if($userId == $owner->getUserId()){
                return true;
            }
        }
        return false;
    }

    public function getFoundCachsByUser($userId)
    {
        $this->getGeocaches();
        $geocachesIdArray = $this->geocaches->getGeocachesIdArray();
        $geocachesStr = '';
        foreach ($geocachesIdArray as $geocacheId) {
            $geocachesStr .= $geocacheId.',';
        }
        $geocachesStr = rtrim($geocachesStr, ',');
        $query = 'SELECT `cache_id` AS `geocacheId` FROM `cache_logs` WHERE `cache_id` in ('.$geocachesStr.') AND `deleted` = 0 AND `user_id` = :1 AND `type` = "1" ';
        $db = DataBaseSingleton::Instance();
        $db->multiVariableQuery($query, (int) $userId);
        $cachesFoundByUser = $db->dbResultFetchAll();
        return $cachesFoundByUser;
    }
    /**
     * check if real cache count in pt is equal stored in db.
     */
    public function checkCacheCount()
    {
        $countQuery = 'SELECT count(*) as `cacheCount` FROM `caches` WHERE `cache_id` IN (SELECT `cacheId` FROM `powerTrail_caches` WHERE `PowerTrailId` =:1)';
        $db = DataBaseSingleton::Instance();
        $db->multiVariableQuery($countQuery, $this->id);
        $answer = $db->dbResultFetch();
        if($answer['cacheCount'] != $this->cacheCount) {
            $updateQuery = 'UPDATE `PowerTrail` SET `cacheCount` =:1  WHERE `id` = :2 ';
            $db->multiVariableQuery($updateQuery, $answer['cacheCount'], $this->id);
        }
    }

    /**
     * disable (set status to 4) geoPaths witch has not enough cacheCount.
     */
    public function disablePowerTrailBecauseCacheCountTooLow()
    {
//        $text = tr('pt227').tr('pt228');
//        print 'pt #'.$this->id.', caches in pt: '.$this->cacheCount.'; min. caches limit: '. $this->getPtMinCacheCountLimit().'<br>';
        if($this->cacheCount < $this->getPtMinCacheCountLimit()){
//            $text .= tr('pt227').tr('pt228');
            print '[test only] geoPath #<a href="powerTrail.php?ptAction=showSerie&ptrail='.$this->id.'">'.$this->id.'</a> (geoPtah cache count='.$this->cacheCount.' is lower than minimum='.$this->getPtMinCacheCountLimit().') <br/>';
//            $db = \lib\Database\DataBaseSingleton::Instance();
//            $queryStatus = 'UPDATE `PowerTrail` SET `status`= :1 WHERE `id` = :2';
//            $db->multiVariableQuery($queryStatus, 4, $pt['id']);
//            $query = 'INSERT INTO `PowerTrail_comments`(`userId`, `PowerTrailId`, `commentType`, `commentText`, `logDateTime`, `dbInsertDateTime`, `deleted`) VALUES
//            (-1, :1, 4, :2, NOW(), NOW(),0)';
//            $db->multiVariableQuery($query, $pt['id'], $text);
//            sendEmail::emailOwners($pt['id'], 4, date('Y-m-d H:i:s'), $text, 'newComment');
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * get minimum cache limit from period of time when ptWas published
     */
    private function getPtMinCacheCountLimit()
    {
        foreach ($this->powerTrailConfiguration['old'] as $date){ //find interval path was created
            if ($this->dateCreated->getTimestamp() >= $date['dateFrom'] && $this->dateCreated->getTimestamp() < $date['dateTo']){ // patch was created here
                return $date['limit'];
            }
        }
        return false;
    }

    /**
     * disable geoPaths, when its WIS > active caches count.
     */
    public function disableUncompletablePt($serverUrl){
        $countQuery = 'SELECT count(*) as `cacheCount` FROM `caches` WHERE `cache_id` IN (SELECT `cacheId` FROM `powerTrail_caches` WHERE `PowerTrailId` =:1) AND `status` = 1';
        $db = DataBaseSingleton::Instance();
        $db->multiVariableQuery($countQuery, $this->id);
        $answer = $db->dbResultFetch();

//      print "active cc: ".$answer['cacheCount'].' / required caches: '. (($this->cacheCount*$this->perccentRequired)/100);

        if($answer['cacheCount'] < ($this->cacheCount*$this->perccentRequired)/100) {
            print '<span style="color: red">[test message only] geoPath #<a href="'.$serverUrl.'powerTrail.php?ptAction=showSerie&ptrail='.$this->id.'">'.$this->id.'</a>should be put in service (uncompletable) cacheCount: '.$answer['cacheCount'].' demand: '. (($this->cacheCount*$this->perccentRequired)/100) . ' </span><br/>';

            //$queryStatus = 'UPDATE `PowerTrail` SET `status`= :1 WHERE `id` = :2';
            // $db->multiVariableQuery($queryStatus, 4, $pt['id']);
            //$query = 'INSERT INTO `PowerTrail_comments`(`userId`, `PowerTrailId`, `commentType`, `commentText`, `logDateTime`, `dbInsertDateTime`, `deleted`) VALUES (-1, :1, 4, :2, NOW(), NOW(),0)';
//            $text = tr('pt227').tr('pt234');
            // $db->multiVariableQuery($query, $pt['id'], $text);
            //emailOwners($pt['id'], 4, date('Y-m-d H:i:s'), $text, 'newComment');
//            d($text);
            return true;
        }
        return false;
    }

    public function getPowerTrailCachesLogsForCurrentUser()
    {
        $db = DataBaseSingleton::Instance();
        $qr = 'SELECT `cache_id`, `date`, `text_html`, `text`  FROM `cache_logs` WHERE `cache_id` IN ( SELECT `cacheId` FROM `powerTrail_caches` WHERE `PowerTrailId` = :1) AND `user_id` = :2 AND `deleted` = 0 AND `type` = 1';
        isset($_SESSION['user_id']) ? $userId = $_SESSION['user_id'] : $userId = 0;
        $db->multiVariableQuery($qr, $this->id, $userId);
        $powerTrailCacheLogsArr = $db->dbResultFetchAll();
        $powerTrailCachesUserLogsByCache = array();
        foreach ($powerTrailCacheLogsArr as $log) {
            $powerTrailCachesUserLogsByCache[$log['cache_id']] = array (
                'date' => $log['date'],
                'text_html' => $log['text_html'],
                'text' => $log['text'],
            );
        }
        return $powerTrailCachesUserLogsByCache;
    }
}