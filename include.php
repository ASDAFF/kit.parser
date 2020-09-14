<?
/**
 * Copyright (c) 11/9/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;
use Bitrix\Main;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');

IncludeModuleLangFile(__FILE__);
global $DB;
$db_type = strtolower($DB->type);
$module_id = 'kit.parser';
$module_status = CModule::IncludeModuleEx($module_id);


CModule::AddAutoloadClasses(
    'kit.parser',
    array(
        'KitParserContentGeneral' => 'classes/general/list_parser.php',
        'CollectedContentParser' => 'classes/general/main_classes.php',
        'CollectedHLCatalogParser' => 'classes/general/main_classes_catalog_HL.php',
        'CollectedXmlParser' => 'classes/general/main_classes_xml.php',
        'CollectedCsvParser' => 'classes/general/main_classes_csv.php',
        'CollectedXlsParser' => 'classes/general/main_classes_xls.php',
        'CollectedXlsCatalogParser' => 'classes/general/main_classes_xls_catalog.php',
        'CollectedXmlCatalogParser' => 'classes/general/main_classes_xml_catalog.php',
        'CollectedCsvCatalogParser' => 'classes/general/main_classes_csv_catalog.php',
        'ParserEventHandler' => 'classes/general/event_handlers.php',
        'KitParserContent' => 'classes/' . $db_type . '/list_parser.php',
        'Export' => 'lib/helper/export.php',
    )
);

include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/kit.parser/classes/phpQuery/phpQuery.php');
include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/kit.parser/classes/general/collected_idna_convert.class.php');
include($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/kit.parser/classes/general/file_get_html.php');

Class RssContentParser extends CollectedCsvCatalogParser
{
    public function __construct()
    {
        CModule::IncludeModule('highloadblock');
        parent::__construct();
    }


    public function collectedParserSetSettings(&$SETTINGS)
    {
        foreach ($SETTINGS as &$v) {
            if (is_array($v)) self::collectedParserSetSettings($v); else {
                $v = htmlentities(htmlspecialcharsBack($v), ENT_QUOTES, SITE_CHARSET);
            }
        }
    }

    public function createFolder()
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/kit.parser/include';
        if (!file_exists($dir)) mkdir($dir, BX_DIR_PERMISSIONS);
    }

    public function auth($check = false, $type = "http")
    {
        $this->check = $check;
        $this->GetAuthForm($check);
    }
}

Class CKitParser
{
    static function startAgent($ID)
    {
        ignore_user_abort(true);
        @set_time_limit(0);
        if (CModule::IncludeModule('iblock') && CModule::IncludeModule('main')): CModule::IncludeModule("highloadblock");
            $parser = KitParserContent::GetByID($ID);
            if (!$parser->ExtractFields('kit_')) $ID = 0;
            if (!file_exists(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt')) file_put_contents(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt', 'start parser ' . $ID); else {
                unset($parser);
                return 'CKitParser::startAgent(' . $ID . ');';
            }
            $rssParser = new RssContentParser();
            $rssParser->startParser(1);
            if (file_exists(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt')) unlink(dirname(__FILE__) . '/include/startAgent' . $ID . '.txt');
            unset($rssParser, $parser);
            return 'CKitParser::startAgent(' . $ID . ');'; endif;
    }

} ?>