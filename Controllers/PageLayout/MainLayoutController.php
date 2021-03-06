<?php

namespace Controllers\PageLayout;

use Controllers\BaseController;
use Controllers\Cron\OnlineUsersController;
use Utils\DateTime\Year;
use Utils\I18n\I18n;
use Utils\Uri\Uri;
use lib\Objects\Admin\GeoCacheApproval;
use lib\Objects\Admin\ReportCommons;
use lib\Objects\GeoCache\PrintList;
use lib\Objects\OcConfig\OcConfig;

class MainLayoutController extends BaseController
{

    const MAIN_TEMPLATE = 'common/mainLayout';

    private $legacyLayout = false;

    public static function init()
    {
        $main = new self();
        $main->initMainLayout();
    }

    public static function initLegacy()
    {
        $main = new self();
        $main->legacyLayout = true;
        $main->initMainLayout();
    }

    public function __construct(){
        parent::__construct();
    }

    public function isCallableFromRouter($actionName)
    {
        // this controller is for internal use only and shouldn't be call by router
        return FALSE;
    }

    public function index()
    {}


    private function initMainLayout()
    {
        global $config; //TODO: refactor


        if($this->isUserLogged()){
            $this->view->setVar('_isUserLogged', true);
            $this->view->setVar('_username', $this->loggedUser->getUserName());
        }else{
            $this->view->setVar('_isUserLogged', false);
            $this->view->setVar('_target',Uri::getCurrentUri(true));
        }


        $this->view->setVar('_siteName', $config['siteName']);
        $this->view->setVar('_keywords', $config['header']['keywords']);
        $this->view->setVar('_favicon', '/images/'.$config['headerFavicon']);
        $this->view->setVar('_appleLogo', $config['header']['appleLogo']);

        $this->view->setVar('_title', "TODO-title"); //TODO!
        $this->view->setVar('_backgroundSeason', $this->view->getSeasonCssName());

        if(!$this->legacyLayout){
            $this->view->addLocalCss(Uri::getLinkWithModificationTime(
                '/tpl/stdstyle/common/mainLayout.css'));
        }

        if(Year::isPrimaAprilisToday()){
            $logo = $config['headerLogo1stApril'];
            $logoTitle = tr('oc_on_all_pages_top_1A');
            $logoSubtitle = tr('oc_subtitle_on_all_pages_1A');
        }else if(date('m') == 12 || date('m') == 1){
            $logo = $config['headerLogoWinter'];
            $logoTitle = tr('oc_on_all_pages_top_' . $config['ocNode']);
            $logoSubtitle = tr('oc_subtitle_on_all_pages_' . $config['ocNode']);
        }else{
            $logo = $config['headerLogo'];
            $logoTitle = tr('oc_on_all_pages_top_' . $config['ocNode']);
            $logoSubtitle = tr('oc_subtitle_on_all_pages_' . $config['ocNode']);
        }

        $this->view->setVar('_mainLogo', '/images/'.$logo);
        $this->view->setVar('_logoTitle', $logoTitle);
        $this->view->setVar('_logoSubtitle', $logoSubtitle);

        $this->view->setVar('_languageFlags',
            I18n::getLanguagesFlagsData($this->view->getLang()));


        $this->view->setVar('_qSearchByOwnerEnabled', $config['quick_search']['byowner']);
        $this->view->setVar('_qSearchByFinderEnabled', $config['quick_search']['byfinder']);
        $this->view->setVar('_qSearchByUserEnabled', $config['quick_search']['byuser']);

        $onlineUsers = OnlineUsersController::getOnlineUsers();
        if(!empty($onlineUsers)){
            $this->view->setVar('_displayOnlineUsers', $config['mainLayout']['displayOnlineUsers']);
            $this->view->setVar('_onlineUsers', OnlineUsersController::getOnlineUsers());
        }else{
            $this->view->setVar('_displayOnlineUsers', false);
        }

        $this->initMenu();

        if(isset($config['license_html'])){
            $this->view->setVar('licenseHtml', $config['license_html']);
        }else{
            $this->view->setVar('licenseHtml', '');
        }
    }

    private function initMenu()
    {

        if(!$this->isUserLogged()){
            // user not authorized
            $this->view->setVar('_isAdmin', false);
            $this->view->setVar('_nonAuthUserMenu',
                $this->getMenu(OcConfig::MENU_NON_AUTH_USER));
        }else{
            // user authorized
            $this->view->setVar('_authUserMenu',
                $this->getMenu(OcConfig::MENU_AUTH_USER));

            // custom user menu
            $this->view->setVar('_customUserMenu',
                $this->getMenu(OcConfig::MENU_CUSTOM_USER));


            if($this->loggedUser->isAdmin()){
                $this->view->setVar('_isAdmin', true);
                $this->view->setVar('_adminMenu',
                    $this->getMenu(OcConfig::MENU_ADMIN_PREFIX));
            }else{
                $this->view->setVar('_isAdmin', false);
                $this->view->setVar('_adminMenu',null);
            }
        }

        $this->view->setVar('_menuBar',
            $this->getMenu(OcConfig::MENU_HORIZONTAL_BAR));

        $this->view->setVar('_footerMenu',
            $this->getMenu(OcConfig::MENU_FOOTER_PREFIX));

        $this->view->setVar('_additionalMenu',
            $this->getMenu(OcConfig::MENU_ADDITIONAL_PAGES));



    }

    /**
     * Prepare the admin menu. Admin menu has counters for special menu entries
     * so it needs special processing.
     *
     * @return array - $key->$url style of menu
     */
    private function adminMenuHandler(&$key, &$url)
    {
        switch($key){
            case 'mnu_reports':
                // add new/active reports counters
                $new_reports = ReportCommons::getReportsCountByStatus(ReportCommons::STATUS_NEW);
                $active_reports = ReportCommons::getReportsCountByStatus(ReportCommons::STATUS_OPEN);
                $key = tr($key) . " ($new_reports/$active_reports)";

                break;
            case 'mnu_pendings':

                $new_pendings = GeoCacheApproval::getWaitingForApprovalCount();
                if($new_pendings > 0){
                    $in_review_count = GeoCacheApproval::getInReviewCount();
                    $waitingForAssigne = $new_pendings - $in_review_count;
                    $key = tr($key) . " ($waitingForAssigne/$new_pendings)";
                }else{
                    $key = tr($key);
                }
                break;
            default:
                $key = tr($key); // by default menu key is just a translation
        }
    }

    /**
     * Prepare the horizontal menu bar.
     *
     * @return array - $key->$url style of menu
     */
    private function horizontalMenuHandler(&$key, &$url)
    {
        switch($key){
            case 'mnu_clipboard':
                // add number of caches in clipboard
                if ( !empty(PrintList::GetContent()) ) {
                    $cachesInClipboard = count(PrintList::GetContent());
                    $key = tr($key)." ($cachesInClipboard)";
                } else {
                    $key = ''; //empty link
                    $url = '';
                }
                break;
            default:
                $key = tr($key); // by default menu key is just a translation
        }
    }

    /**
     * Prepare the auth user menu.
     *
     * @return array - $key->$url style of menu
     */
    private function authUserMenuHandler(&$key, &$url)
    {
        switch($key){
            case 'mnu_geoPaths':
                // disable geopaths link if disabled in config
                if ( !OcConfig::isPowertrailsEnabled() ) {
                    $url = '';
                    break;
                }
            default:
                $key = tr($key); // by default menu key is just a translation
        }
    }

    private function getMenu($menuPrefix)
    {
        $menu = [];
        foreach(OcConfig::getMenu($menuPrefix) as $key => $url){

            switch($menuPrefix){
                case OcConfig::MENU_ADMIN_PREFIX:
                    $this->adminMenuHandler($key, $url);
                    break;

                case OcConfig::MENU_HORIZONTAL_BAR:
                    $this->horizontalMenuHandler($key, $url);
                    break;

                case OcConfig::MENU_AUTH_USER:
                    $this->authUserMenuHandler($key, $url);
                    break;

                default:
                    $key = tr($key);
                    if(!is_array($url)){
                        $url = htmlspecialchars($url);
                    }
            }

            if(empty($url)){
                continue;
            }

            $menu[$key] = $url;
        }
        return $menu;
    }

}
