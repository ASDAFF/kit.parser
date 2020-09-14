<?
/**
 * Copyright (c) 12/9/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("kit.parser")!="D")
{
    $parent = 'global_menu_content';
    if(\Bitrix\Main\Loader::includeModule('sotbit.missshop') && $APPLICATION->GetGroupRight("kit.missshop")!="D")
    {
        $parent = 'global_menu_missshop';
    }
    if(\Bitrix\Main\Loader::includeModule('sotbit.mistershop') && $APPLICATION->GetGroupRight("kit.mistershop")!="D")
    {
        $parent = 'global_menu_mistershop';
    }
    if(\Bitrix\Main\Loader::includeModule('sotbit.b2bshop') && $APPLICATION->GetGroupRight("kit.b2bshop")!="D")
    {
        $parent = 'global_menu_b2bshop';
    }

    $aMenu = array(
        "parent_menu" => $parent,//"global_menu_content",
        "section" => "kit.parser",
        "sort" => 200,
        "text" => GetMessage("mnu_kit_parser_sect"),
        "title" => GetMessage("mnu_kit_parser_sect_title"),
        "url" => "list_parser_admin.php?lang=".LANGUAGE_ID,
        "icon" => "kit_parser_menu_icon",
        "page_icon" => "kit_parser_page_icon",
        "items_id" => "menu_kit.parser",
        "items" => array(
            array(
                "text" => GetMessage("mnu_kit_list_parser"),
                "url" => "list_parser_admin.php?lang=".LANGUAGE_ID,
                "more_url" => array("list_parser_admin.php", "parser_edit.php"),
                "title" => GetMessage("mnu_kit_list_parser_alt")
            ),
            array(
                "text" => GetMessage("mnu_kit_list_result"),
                "url" => "list_parser_result_admin.php?lang=".LANGUAGE_ID,
                "more_url" => array(),
                "title" => GetMessage("mnu_kit_list_result")
            ),
            array(
                "text" => GetMessage("mnu_kit_parser_export"),
                "url" => "parser_export.php?lang=".LANGUAGE_ID,
                "more_url" => array(),
                "title" => GetMessage("mnu_kit_parser_export")
            ),
        )
    );

    return $aMenu;
}
return false;
?>