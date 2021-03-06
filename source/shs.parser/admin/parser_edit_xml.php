<?
use Bitrix\Seo\Engine;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Path;

\Bitrix\Main\Loader::includeModule('seo');
\Bitrix\Main\Loader::includeModule('socialservices');
global $shs_IBLOCK_ID;

/***
 **** Основной таб
 ***/
$tabControl->BeginNextTab();
//$arEncoding['reference'] = array('utf-8', 'windows-1251');
//$arEncoding['reference_id'] = array('utf-8', 'windows-1251');
$arType['reference'] = array('html', 'text');
$arType['reference_id'] = array('html', 'text');
$arMode['reference'] = array('debug', 'work');
$arMode['reference_id'] = array('debug', 'work');

$arrFilterCircs['reference'] = array(GetMessage("parser_filter_equally"), GetMessage("parser_filter_strpos"), GetMessage("parser_filter_stripos"));
$arrFilterCircs['reference_id'] = array('equally', 'strpos', 'stripos');

$arTypeAddAutoProps['reference'] = array(GetMessage("parser_type_string"), GetMessage("parser_type_list"));
$arTypeAddAutoProps['reference_id'] = array('S', 'L');
$arFieldWriteIdSection['reference'] = array(GetMessage("field_xml_id"), GetMessage("field_ext"));
$arFieldWriteIdSection['reference_id'] = array('XML_ID', 'EXT_FIELD');

$arStore = array();
$arrAdditStores = array();
$arAmount = array();
$arAmount['reference']=array(GetMessage('parser_amount_from_file'));
$arAmount['reference_id']=array('from_file');

if(CModule::IncludeModule("catalog")){
    $arAmount['reference'][]=GetMessage('parser_amount_update_from_stores');
    $arAmount['reference_id'][]='from_stores';

    $selectFields = array('ID','TITLE');
    $filter = array("ACTIVE" => "Y");
    $resStore = CCatalogStore::GetList(array(),$filter,false,false,$selectFields);
    while($store = $resStore->Fetch())
    {
        $arStore['reference_id'][] = $store['ID'];
        $arStore['reference'][] = $store['TITLE'];
        if($store["ID"]==$shs_SETTINGS['store']['list'])
            continue;
        $arrAdditStores["reference_id"][] = $store["ID"];
        $arrAdditStores["reference"][] = $store["TITLE"];
    }
}

$arOfferLoad['reference'] = array(GetMessage("parser_offer_load_no"), GetMessage("parser_offer_load_container"), GetMessage("parser_offer_load_one"));
$arOfferLoad['reference_id'] = array('', 'table', 'one');

$arrUniqCategory["reference"] = array(GetMessage('parser_select_uniq_category_id'), GetMessage('parser_select_uniq_category_file'), GetMessage('parser_select_uniq_category_parser_id'));
$arrUniqCategory["reference_id"] = Array("id_category", "link_xml_file", "id_parser");

$arUpdate['reference'] = array(GetMessage("parser_update_N"), GetMessage("parser_update_Y"), GetMessage("parser_update_empty"));
$arUpdate['reference_id'] = array('N', 'Y', 'empty');

$arAction['reference'] = array(GetMessage("parser_action_N"), GetMessage("parser_action_A"), GetMessage("parser_action_D"), GetMessage("parser_action_NULL"));
$arAction['reference_id'] = array('N', 'A', 'D', 'NULL');

//$arPriceTerms['reference'] = array(GetMessage("parser_price_terms_no"), GetMessage("parser_price_terms_up"), GetMessage("parser_price_terms_down"));
//$arPriceTerms['reference_id'] = array('', 'up', 'down');

$arPriceTerms['reference'] = array(GetMessage("parser_price_terms_no"), GetMessage("parser_price_terms_delta"));
$arPriceTerms['reference_id'] = array('', 'delta');

$arPriceUpDown['reference'] = array(GetMessage("parser_price_updown_no"), GetMessage("parser_price_updown_up"), GetMessage("parser_price_updown_down"));
$arPriceUpDown['reference_id'] = array('', 'up', 'down');

$arPriceValue['reference'] = array(GetMessage("parser_price_percent"), GetMessage("parser_price_abs_value"));
$arPriceValue['reference_id'] = array('percent', 'value');

$arAuthType['reference'] = array(GetMessage("parser_auth_type_form"), GetMessage("parser_auth_type_http"));
$arAuthType['reference_id'] = array('form', 'http');


$hideCatalog = false;
$arPricesNames = array();
$selectedPriceName = '';
if($isCatalog && CModule::IncludeModule('catalog') && CModule::IncludeModule('currency')/* && (($shs_IBLOCK_ID && CCatalog::GetList(Array("name" => "asc"), Array("ACTIVE"=>"Y", "ID"=>$shs_IBLOCK_ID))->Fetch()) || !$shs_IBLOCK_ID)*/)
{
    $selectedPriceName = CCatalogGroup::GetByID($shs_SETTINGS['catalog']['price_type']);
    $selectedPriceName = $selectedPriceName["NAME_LANG"]?:$selectedPriceName["NAME"];
    $dbPriceType = CCatalogGroup::GetList(
        array("SORT" => "ASC"),
        array()
    );

    while ($arPriceTypes = $dbPriceType->Fetch())
    {
        $arPriceType["reference"][] = $arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        $arPriceType["reference_id"][] = $arPriceTypes["ID"];
        $arPricesNames[$arPriceTypes["ID"]]=$arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        if($arPriceTypes["ID"]==$shs_SETTINGS['catalog']['price_type'])
            continue;
        $arAdditPriceType["reference"][] = $arPriceTypes["NAME_LANG"]?:$arPriceTypes["NAME"];
        $arAdditPriceType["reference_id"][] = $arPriceTypes["ID"];
    }

    $arConvertCurrency["reference"][] = GetMessage("parser_convert_no");
    $arConvertCurrency["reference_id"][] = "";

    $arPriceOkrug["reference"] = array(GetMessage("parser_price_okrug_no"), GetMessage("parser_price_okrug_up"), GetMessage("parser_price_okrug_ceil"), GetMessage("parser_price_okrug_floor"));
    $arPriceOkrug["reference_id"] = array("", "up", "ceil", "floor");

    $lcur = CCurrency::GetList(($by="name"), ($order1="asc"), LANGUAGE_ID);
    while($lcur_res = $lcur->Fetch())
    {
        $arCurrency["reference"][] = $lcur_res["FULL_NAME"];
        $arCurrency["reference_id"][] = $lcur_res["CURRENCY"];
        $arConvertCurrency["reference"][] = $lcur_res["FULL_NAME"];
        $arConvertCurrency["reference_id"][] = $lcur_res["CURRENCY"];
    }
    $info = CModule::CreateModuleObject('catalog');

    if(!CheckVersion("14.0.0", $info->MODULE_VERSION))
    {
        $dbResultList = CCatalogMeasure::getList(array(), array(), false, false, array("ID", "CODE", "MEASURE_TITLE", "SYMBOL_INTL", "IS_DEFAULT"));
        while($arMeasure = $dbResultList->Fetch())
        {
            $arAllMeasure["reference_id"][] = $arMeasure["ID"];
            $arAllMeasure["reference"][] = $arMeasure["MEASURE_TITLE"];
        }
    }

    $arVATRef = CatalogGetVATArray(array(), true);

}else $hideCatalog = true;

/*
if(isset($arrPropDop))
{
    $arrPropField['REFERENCE'] = array_merge($arrPropField['REFERENCE'], $arrPropDop["REFERENCE"]);
    $arrPropField['REFERENCE_ID'] = array_merge($arrPropField['REFERENCE_ID'], $arrPropDop["REFERENCE_ID"]);
}*/

$arrActionProps['REFERENCE'] = array(GetMessage("parser_action_props_delete"), GetMessage("parser_action_props_add_begin"), GetMessage("parser_action_props_add_end"), GetMessage("parser_action_props_to_lower"));
$arrActionProps['REFERENCE_ID'] = array("delete", "add_b", "add_e", "lower");

$arrFieldsCategory = array();
$arrFieldsCategoryS = array();
if(isset($shs_IBLOCK_ID) && !empty($shs_IBLOCK_ID)){
    $res = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.intval($shs_IBLOCK_ID).'_SECTION'));
    while($one = $res->fetch()){
        $arrFieldsCategory[$one['FIELD_NAME']] = $one['FIELD_NAME'];
        $arrFieldsCategoryS['REFERENCE'][] = $one['FIELD_NAME'];
        $arrFieldsCategoryS['REFERENCE_ID'][] = $one['FIELD_NAME'];
    }
}

$disabled = false;
$disabledType = false;
unset($arrDateActive['REFERENCE'][2]);
unset($arrDateActive['REFERENCE_ID'][2]);
$arrDate = ParseDateTime($shs_START_LAST_TIME_X, "YYYY.MM.DD HH:MI:SS");
if($shs_TYPE_OUT)$disabledType  = 'disabled=""';
if($shs_TYPE)$disabled  = 'disabled=""';

$TYPE_OUT = strtoupper($shs_TYPE_OUT?$shs_TYPE_OUT:$_GET["type_out"]);

$hlFields = array();
$arColumns = array();
$arSelectColumns = array();
if($shs_IBLOCK_ID && $TYPE_OUT == 'HL')
{
    $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($shs_IBLOCK_ID)->fetch();
    if($hlblock)
    {
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $arColumns = array_keys($entity->getFields());
        unset($arColumns[0]);

        foreach($arColumns as $column)
        {
            $arSelectColumns['REFERENCE'][] = $column;
            $arSelectColumns['REFERENCE_ID'][] = $column;
        }
    }
}

?>
    <tr>
        <td><?echo GetMessage("parser_type")?></td>
        <td><?=SelectBoxFromArray('TYPE', $arTypeParser, $shs_TYPE?$shs_TYPE:$_GET["type"], "", $disabled);?>
            <?if($disabled):?><input type="hidden" name="TYPE" value="<?=$shs_TYPE?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_type_out")?></td>
        <td><?=SelectBoxFromArray('TYPE_OUT', $arTypeOut, $shs_TYPE_OUT?$shs_TYPE_OUT:$_GET["type_out"], "", $disabledType);?>
            <?if($disabledType):?><input type="hidden" name="TYPE_OUT" value="<?=$shs_TYPE_OUT?>" /><?endif;?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_mode_descr_yml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_mode")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][mode]', $arMode, $shs_SETTINGS["catalog"]["mode"]?$shs_SETTINGS["catalog"]["mode"]:"debug", "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_mode_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_act")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($shs_ACTIVE == "Y" || !$ID) echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_sort")?></td>
        <td><input type="text" name="SORT" value="<?echo !$ID?"100":$shs_SORT;?>" size="4"></td>
    </tr>
<?if(isset($arCategory) && !empty($arCategory)):?>
    <tr>
        <td><?echo GetMessage("parser_category_title")?></td>
        <td><?=SelectBoxFromArray('CATEGORY_ID', $arCategory, isset($shs_CATEGORY_ID)?$shs_CATEGORY_ID:$parentID, GetMessage("parser_category_select"), "id='category' style='width:262px'");?></td>
    </tr>
<?endif;?>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_name")?></td>
        <td><input type="text" name="NAME" value="<?echo $shs_NAME;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_xml_catalog")?></td>
        <td><input style="style="float: left;" id="RSS" type="text" name="RSS" value="<?echo $shs_RSS;?>" size="80" maxlength="500"><span class="adm-btn" id="butoon_rss"><?=GetMessage("button_caption")?></span>
            <?
            CAdminFileDialog::ShowScript(Array
                (
                    "event" => "OpenFileXml",
                    "arResultDest" => Array("FUNCTION_NAME" => "OpenFileXmlResult"),
                    "arPath" => Array(),
                    "select" => 'F',
                    "operation" => 'O',
                    "showUploadTab" => true,
                    "showAddToMenuTab" => false,
                    "fileFilter" => '',
                    "allowAllFiles" => true,
                    "saveConfig" => true
                )
            );
            ?>
            <script>
                document.getElementById("butoon_rss").onclick = OpenFileXml;
                var OpenFileXmlResult = function(filename,path,site)
                {
                    if(path != "/") path = path + "/";
                    document.getElementById('RSS').value = path + filename;
                }
            </script>
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top"><?echo GetMessage("parser_url_xml_dop")?></td>
        <td style="vertical-align:top">
            <textarea style="float: left" id="URL_DOP" name="SETTINGS[catalog][url_dop]" cols="65" rows="5"><?=$shs_SETTINGS["catalog"]["url_dop"]?></textarea><span class="adm-btn" id="butoon_url_dop"><?=GetMessage("button_caption")?></span>
            <?
            CAdminFileDialog::ShowScript(Array
                (
                    "event" => "OpenFileXmlUrlDop",
                    "arResultDest" => Array("FUNCTION_NAME" => "OpenFileXmlUrlDopResult"),
                    "arPath" => Array(),
                    "select" => 'F',
                    "operation" => 'O',
                    "showUploadTab" => true,
                    "showAddToMenuTab" => false,
                    "fileFilter" => '',
                    "allowAllFiles" => true,
                    "saveConfig" => true
                )
            );
            ?>
            <script>
                document.getElementById("butoon_url_dop").onclick = OpenFileXmlUrlDop;
                function trim()
                {
                    return this.replace(/^\n+|\n+$/g, '');
                }
                var OpenFileXmlUrlDopResult = function(filename,path,site)
                {
                    var str = document.getElementById('URL_DOP').value;
                    str = str.trim();
                    if (path != "/") path = path + "/";
                    document.getElementById('URL_DOP').value = str + "\n" + path + filename;
                }
            </script>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_url_dop_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_part_xml")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[part_xml]" value="Y"<?if($shs_SETTINGS["part_xml"] == "Y") echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_part_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?php
if((isset($shs_TYPE) && $shs_TYPE == 'xml_catalo') || (isset($_GET['type']) && $_GET['type'] == 'xml_catalo')){
    $p = new ShsParserContent();
    $arParser = array();
    $rsParser = $p->GetList(array('ID'=>'ASC'),array('TYPE'=>'catalog'));
    while($rs = $rsParser->fetch()){
        $arParser['REFERENCE'][] = '['.$rs['ID'].'] '.$rs['NAME'];
        $arParser['REFERENCE_ID'][] = $rs['ID'];
    }
    unset($p);
    unset($rsParser);
    ?>
    <tr>
        <td>
            <?echo GetMessage("parser_parser_id_detail")?>
        </td>
        <td>
            <?=SelectBoxFromArray('SETTINGS[catalog][parser_id_detail]', $arParser, $shs_SETTINGS['catalog']['parser_id_detail'], GetMessage("parser_parser_id_detail"), "id='SETTINGS[catalog][parser_id_detail]' style='width:262px' ");?>
        </td>
    </tr>
    <?php
}
?>
    <tr>
        <td><span class="required">*</span><?echo GetMessage("parser_iblock_id_catalog")?></td>
        <td><?=SelectBoxFromArray('IBLOCK_ID', $arIBlock, $shs_IBLOCK_ID, GetMessage("parser_iblock_id"), "id='iblock' style='width:262px' ");?>
            <?/*?><?if($disabled):?><input type="hidden" name="IBLOCK_ID" value="<?=$shs_IBLOCK_ID?>" /><?endif;*/?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_iblock_id_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?if($TYPE_OUT == 'IBLOCK' || empty($TYPE_OUT)):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_add_section")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][add_parser_section]" value="Y"<?if($shs_SETTINGS['catalog']['add_parser_section'] == 'Y') echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_add_section_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_section_id")?></td>
        <td><?=SelectBoxFromArray('SECTION_ID', $arSection, $shs_SECTION_ID, GetMessage("parser_section_id"), "id='section' style='width:262px'");?></td>
    </tr>
<?endif;?>
    <tr>
        <td><?echo GetMessage("parser_encoding")?></td>
        <td><?=SelectBoxFromArray('ENCODING', $arEncoding, $shs_ENCODING);?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_encoding_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_step")?></td>
        <td><input type="text" name="SETTINGS[catalog][step]" value="<?echo $shs_SETTINGS["catalog"]["step"]?$shs_SETTINGS["catalog"]["step"]:300;?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_start_last_time")?></td>
        <td><input type="text" disabled name="START_LAST_TIME_X" value="<?echo $arrDate[DD].'.'.$arrDate[MM].'.'.$arrDate[YYYY].' '.$arrDate[HH].':'.$arrDate[MI].':'.$arrDate[SS];?>" size="20"></td>
    </tr>


<?
//********************
//Настройки парсинга каталога
//********************
if($TYPE_OUT == 'IBLOCK' || empty($TYPE_OUT)):
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_category_description")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_section_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_category]" value="<?echo isset($shs_SETTINGS["catalog"]["selector_category"])?$shs_SETTINGS["catalog"]["selector_category"]:"categories category";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_section_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_name_section_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][attr_category]" value="<?echo isset($shs_SETTINGS["catalog"]["attr_category"])?$shs_SETTINGS["catalog"]["attr_category"]:"";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_attr_category_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_id_section_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][attr_id_category]" value="<?echo isset($shs_SETTINGS["catalog"]["attr_id_category"])?$shs_SETTINGS["catalog"]["attr_id_category"]:"[id]";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_attr_id_category_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_id_parrent_category_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][attr_id_parrent_category]" value="<?echo isset($shs_SETTINGS["catalog"]["attr_id_parrent_category"])?$shs_SETTINGS["catalog"]["attr_id_parrent_category"]:"[parentId]";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_attr_id_parrent_category_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_field_id_category_catalog")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][field_id_category]', $arFieldWriteIdSection, $shs_SETTINGS["catalog"]["field_id_category"]?$shs_SETTINGS["catalog"]["field_id_category"]:"XML_ID", "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_field_id_category_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_description_section_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][description_category]" value="<?echo isset($shs_SETTINGS["catalog"]["description_category"])?$shs_SETTINGS["catalog"]["description_category"]:"";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_type_descr_category_catalog")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][description_category_type]', $arType, $shs_SETTINGS["catalog"]["description_category_type"]?$shs_SETTINGS["catalog"]["description_category_type"]:"text", "", "");?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_pic_section_catalog")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][pic_category]" value="<?echo isset($shs_SETTINGS["catalog"]["pic_category"])?$shs_SETTINGS["catalog"]["pic_category"]:"";?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_category_fields_category_head")?></td>
    </tr>
    <?if(isset($shs_SETTINGS["catalog"]["fields_category"]) && !empty($shs_SETTINGS["catalog"]["fields_category"])):?>
    <?foreach($shs_SETTINGS["catalog"]["fields_category"] as $key => $val):?>
        <?
        $val = trim($val);
        if(!$val) continue 1;
        ?>
        <tr class="fields_category_<?php echo $key;?>" data-id="<?php echo $key;?>">
            <td class="adm-detail-content-cell-l"><?php echo $key;?></td>
            <td class="adm-detail-content-cell-r"><input type="text" maxlength="500" size="50" value="<?=$val;?>" name="SETTINGS[catalog][fields_category][<?=$key;?>]">
            </td>
        </tr>
    <?endforeach;?>
<?endif?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrFieldCategory', $arrFieldsCategoryS, "", '', "");?>
            <input type="submit" id="addFieldCategory" name="refresh" value="<?=GetMessage("parser_add_rss_dop_button")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_category_main_description")?></td>
    </tr>
    <?if(isset($shs_SETTINGS["catalog"]["id_category_main"]) && !empty($shs_SETTINGS["catalog"]["id_category_main"])):?>
    <?$count_url = 0;?>
    <?foreach($shs_SETTINGS["catalog"]["id_category_main"] as $key => $dop_rss):?>
        <?
        $dop_rss = trim($dop_rss);
        if(!$dop_rss) continue 1;
        $count_url++;
        ?>
        <tr class="admin_tr_rss_dop" data-id="<?php echo $key;?>">
            <td class="adm-detail-content-cell-l"><?=GetMessage("parser_id_category_main").$count_url;?></td>
            <td class="adm-detail-content-cell-r"><input type="text" maxlength="500" size="50" value="<?=$dop_rss;?>" name="SETTINGS[catalog][id_category_main][<?=$key;?>]">
                <?=SelectBoxFromArray("SETTINGS[catalog][section_main][".$key."]", $arSection, isset($shs_SETTINGS["catalog"]["section_main"][$key])?$shs_SETTINGS["catalog"]["section_main"][$key]:"", GetMessage("parser_section_id"), "style='width:262px;'");?>
                <a class="id_category_main" href="#"><?=GetMessage("parser_caption_detete_button");?></a>
            </td>
        </tr>
    <?endforeach;?>
<?endif?>
    <tr>
        <td colspan="2" align="center">
            <span id="loadMainCategory" class="adm-btn"><?=GetMessage("parser_add_rss_dop_button")?></span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_id_category_main_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_id_category_main_filter")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][section_main_filter]" value="Y"<?if($shs_SETTINGS["catalog"]["section_main_filter"] == "Y") echo " checked"?>>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_id_category_main_filter_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    //********************
    //Настройки парсинга товаров
    //********************
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_offer_description")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_preview_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector]" value="<?echo isset($shs_SETTINGS["catalog"]["selector"])?$shs_SETTINGS["catalog"]["selector"]:"offers offer";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_catalog_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_preview_id_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][id_selector]" value="<?echo isset($shs_SETTINGS["catalog"]["id_selector"])?$shs_SETTINGS["catalog"]["id_selector"]:"[id]";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_id_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_categoryId_catalog")?></td>
        <td><input type="text" name="SETTINGS[catalog][id_section]" value="<?echo isset($shs_SETTINGS["catalog"]["id_section"])?$shs_SETTINGS["catalog"]["id_section"]:"categoryId";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_categoryId_descr_catalog")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_name_catalog_xml")?></td>
        <td><input type="text" name="SETTINGS[catalog][name]" value="<?echo isset($shs_SETTINGS["catalog"]["name"])?$shs_SETTINGS["catalog"]["name"]:"name";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_name_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr>
        <td><?echo GetMessage("parser_preview_price").$selectedPriceName.':'?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_price]" value="<?echo isset($shs_SETTINGS["catalog"]["preview_price"])?$shs_SETTINGS["catalog"]["preview_price"]:"price";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_procurement_price").':'?></td>
        <td><input type="text" name="SETTINGS[catalog][procurement_price]" value="<?echo isset($shs_SETTINGS["catalog"]["procurement_price"])?$shs_SETTINGS["catalog"]["procurement_price"]:"";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_price_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?php
    if((isset($shs_TYPE) && $shs_TYPE == 'xml_catalo') || (isset($_GET['type']) && $_GET['type'] == 'xml_catalo')){ ?>
        <tr>
            <td class="field-name" width="40%"><?echo GetMessage("parser_detail_link")?></td>
            <td width="60%"><input type="text" name="SETTINGS[catalog][detail_link]" value="<?echo isset($shs_SETTINGS["catalog"]["detail_link"])?$shs_SETTINGS["catalog"]["detail_link"]:"";?>" size="40" maxlength="250"></td>
        </tr>
    <?php } ?>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['prices_preview']) && !empty($shs_SETTINGS['prices_preview'])){
        foreach($shs_SETTINGS['prices_preview'] as $id_price => $price){
            ?>
            <tr class="adittional_preview_prices_id_<?php echo $id_price;?>">
                <td><?php echo GetMessage('parser_preview_price').$price['name'].'['.$id_price.']:';?></td>
                <td>
                    <input type="text" name="SETTINGS[prices_preview][<?php echo $id_price;?>][value]" value="<?echo $price["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price;?>">Delete</a>
                    <input type="hidden" name="SETTINGS[prices_preview][<?php echo $id_price;?>][name]" value="<?echo $price["name"];?>">
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("shs_parser_addit_prices"), "");?>
            <input type="submit" id="addPrice" name="refresh" value="<?=GetMessage("shs_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_price_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_preview_count_xml")?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_count]" value="<?echo $shs_SETTINGS["catalog"]["preview_count"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_count_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_xml_text_preview_selector")?></td>
        <td><input type="text" name="SETTINGS[catalog][preview_text_selector]" value="<?echo isset($shs_SETTINGS["catalog"]["preview_text_selector"])?$shs_SETTINGS["catalog"]["preview_text_selector"]:"preview";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_xml_text_description_selector")?></td>
        <td><input type="text" name="SETTINGS[catalog][detail_text_selector]" value="<?echo isset($shs_SETTINGS["catalog"]["detail_text_selector"])?$shs_SETTINGS["catalog"]["detail_text_selector"]:"description";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_description_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_first_img_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][preview_picture]" value="<?echo isset($shs_SETTINGS["catalog"]["preview_picture"])?$shs_SETTINGS["catalog"]["preview_picture"]:"picture:eq(0)";?>" size="40" maxlength="255" /></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_detail_first_img_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][detail_picture]" value="<?echo isset($shs_SETTINGS["catalog"]["detail_picture"])?$shs_SETTINGS["catalog"]["detail_picture"]:"picture:eq(0)";?>" size="40" maxlength="255" /></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_preview_first_img_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    //********************
    //��������
    //********************
    $tabControl->BeginNextTab();
    ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_image")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_more_image_prop")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][more_image_props]', $arrPropFile, $shs_SETTINGS["catalog"]["more_image_props"], GetMessage("parser_prop_id"), "class='image_props'");?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_selector_more_image_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][more_image]" value="<?echo isset($shs_SETTINGS["catalog"]["more_image"])?$shs_SETTINGS["catalog"]["more_image"]:"picture";?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_more_image_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_default_props")?></td>
    </tr>
    <?if(isset($shs_SETTINGS["catalog"]["default_prop"]) && !empty($shs_SETTINGS["catalog"]["default_prop"])):?>
    <?foreach($shs_SETTINGS["catalog"]["default_prop"] as $code=>$val):
        $val = trim($val);
        if(!$val) continue 1;
        ?>
        <tr>
            <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
            <td width="60%">
                <?if($arrPropDop['REFERENCE_TYPE'][$code]=="L"):
                    ?>
                    <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], $shs_SETTINGS["catalog"]["default_prop"][$code], "", "");?>
                <?elseif($arrPropDop['USER_TYPE'][$code]=="directory"):?>
                    <?=SelectBoxFromArray('SETTINGS[catalog][default_prop]['.$code.']', $arrPropDop["LIST_VALUES"][$code], $shs_SETTINGS["catalog"]["default_prop"][$code], "", "");?>
                <?else:?>
                    <input type="text" <?if(!$shs_SETTINGS["catalog"]["default_prop"][$code]):?>placeholder="<?=GetMessage("parser_prop_default")?>"<?endif;?> name="SETTINGS[catalog][default_prop][<?=$code?>]" value="<?=$shs_SETTINGS["catalog"]["default_prop"][$code]?>" />
                <?endif?>
            </td>
        </tr>
    <?endforeach;?>
    <?endif;
    $arrPropDopDefault = $arrPropDop;
    unset($arrPropDopDefault['REFERENCE'][0]);
    unset($arrPropDopDefault['REFERENCE_ID'][0]);

    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDefault', $arrPropDopDefault, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadPropDefault" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_filter_props")?></td>
    </tr>

    <tr>
        <td width="40%"><?echo GetMessage("parser_props_filter")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][enable_props_filter]" value="Y"<?if($shs_SETTINGS["catalog"]["enable_props_filter"] == "Y") echo " checked"?>>
        </td>
    </tr>

    <?
    if(isset($shs_SETTINGS["props_filter_value"]) && count($shs_SETTINGS["props_filter_value"]) > 0)
    {
        foreach($shs_SETTINGS["props_filter_value"] as $id => $propsfilter)
        {
            foreach($propsfilter as $code => $val)
            {
                if(empty($code) || empty($val)) continue 1;
                ?>
                <tr>
                    <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]</td>
                    <td width="60%">
                        <?=SelectBoxFromArray("SETTINGS[props_filter_circs][$id][$code]", $arrFilterCircs, $shs_SETTINGS["props_filter_circs"][$id][$code]?$shs_SETTINGS["props_filter_circs"][$id][$code]:"equally", "", "");?>
                        <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[props_filter_value][<?=$id?>][<?=$code?>]" value="<?=$val?>">
                        <a href="#" class="prop_delete">Delete</a>
                    </td>
                </tr>
                <?
            }
        }
    }?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropsFilter', $arrPropDop, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadFilterProps" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_filter_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="heading" id="header_selector_prop">
        <td colspan="2"><?echo GetMessage("parser_selector_props_xml")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_props_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($shs_SETTINGS["catalog"]["selector_prop"]) && !empty($shs_SETTINGS["catalog"]["selector_prop"])):?>
    <?foreach($shs_SETTINGS["catalog"]["selector_prop"] as $code=>$val):
        $val = trim($val);
        if(!$val) continue 1;
        ?>
        <tr>
            <td width="40%"><?=$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
            <td width="60%">
                <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][selector_prop][<?=$code?>]" value="<?=$shs_SETTINGS["catalog"]["selector_prop"][$code]?>">
            </td>
        </tr>
    <?endforeach?>
<?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDop', $arrPropDop, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadDopProp" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr style="display:none">
        <td colspan="2"><input type="hidden" id="delete_selector_prop" name="SETTINGS[catalog][delete_selector_prop]" value="<?=$shs_SETTINGS["catalog"]["delete_selector_prop"]?>" /></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_prop_detail_preview_descr_file_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_find_props_xml")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_add_auto_props")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][add_auto_props]" value="Y"<?if($shs_SETTINGS["catalog"]["add_auto_props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_add_auto_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find_props_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_props]" value="<?echo isset($shs_SETTINGS["catalog"]["selector_find_props"])?$shs_SETTINGS["catalog"]["selector_find_props"]:'param';?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_find_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_attr_auto_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][attr_auto_props]" value="<?echo isset($shs_SETTINGS["catalog"]["attr_auto_props"])?$shs_SETTINGS["catalog"]["attr_auto_props"]:'[name]';?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_attr_auto_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_attr_value_auto_props")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_attr_value_auto_props]" value="<?echo isset($shs_SETTINGS["catalog"]["selector_attr_value_auto_props"])?$shs_SETTINGS["catalog"]["selector_attr_value_auto_props"]:'';?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_attr_value_auto_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("type_add_auto_props")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][type_auto_props]', $arTypeAddAutoProps, $shs_SETTINGS["catalog"]["type_auto_props"]?$shs_SETTINGS["catalog"]["type_auto_props"]:"S", "", "");?></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("type_add_auto_props_description")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="tr_find_prop">
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_find_props_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_find_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr class="tr_find_prop">
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr style="display:none">
        <td colspan="2"><input type="hidden" id="delete_find_prop" name="SETTINGS[catalog][delete_find_prop]" value="<?=$shs_SETTINGS["catalog"]["delete_find_prop"]?>" /></td>
    </tr>
    <tr class="heading" id="header_find_prop">
        <td colspan="2"><?echo GetMessage("parser_add_delete_symb_props")?></td>
    </tr>
    <?if(isset($shs_SETTINGS["catalog"]["action_props_val"]) && !empty($shs_SETTINGS["catalog"]["action_props_val"])):?>
    <?foreach($shs_SETTINGS["catalog"]["action_props_val"] as $code=>$arVal):
        foreach($arVal as $i=>$val):
            $val = trim($val);
            if(!$val) continue 1;
            ?>
            <tr>
                <td width="40%"><?=($code=="SOTBIT_PARSER_NAME_E")?GetMessage("parser_SOTBIT_PARSER_NAME_E"):$arrPropDop['REFERENCE_CODE_NAME'][$code]?>&nbsp;<?if($code=="SOTBIT_PARSER_NAME_E"):?><?else:?>[<?=$code?>]<?endif;?>:</td>
                <td width="60%"><input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[catalog][action_props_val][<?=$code?>][]" value="<?=$val?>">&nbsp; <?=SelectBoxFromArray('SETTINGS[catalog][action_props]['.$code.']['.$i.']', $arrActionProps, $shs_SETTINGS["catalog"]["action_props"][$code][$i], GetMessage("shs_parser_select_action_props"), "");?> <a class="find_delete" href="#">Delete</a></td>
            </tr>
        <?endforeach;?>
    <?endforeach;?>
<?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropField', $arrPropField, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadPropField" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr class="tr_find_prop">
        <?
        ?>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("shs_parser_action_props_descr")?>
            <?=EndNote();?>
        </td>
    </tr>


<?
endif;

if($TYPE_OUT == 'HL'):
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_hl_product")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector]" value="<?echo $shs_SETTINGS["catalog"]["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <?
    foreach($arColumns as $column):
        ?>
        <tr>
            <td><?=$column?></td>
            <td><input type="text" name="SETTINGS[highload][<?=$column;?>]" value="<?echo $shs_SETTINGS["highload"][$column]?$shs_SETTINGS["highload"][$column]:'';?>" size="40" maxlength="250"></td>
        </tr>
    <?
    endforeach;
endif;
//********************
//�������� �������
//********************
if(!$hideCatalog):
    $tabControl->BeginNextTab();
    ?>
    <?//if($isOfferCatalog):?>
    <tr>
        <td><?echo GetMessage("parser_cat_price_offer")?></td>
        <td><input class="bool-delete" type="checkbox" name="SETTINGS[catalog][cat_vat_price_offer]" value="Y"<?if($shs_SETTINGS["catalog"]["cat_vat_price_offer"] == "Y") echo " checked"?> /></td>
    </tr>
    <?//endif;?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_price_type")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][price_type]', $arPriceType, $shs_SETTINGS["catalog"]["price_type"]?$shs_SETTINGS["catalog"]["price_type"]:1, "", "");?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_cat_vat_id")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][cat_vat_id]', $arVATRef, $shs_SETTINGS["catalog"]["cat_vat_id"], "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_cat_vat_included")?></td>
        <td><input class="bool-delete" type="checkbox" name="SETTINGS[catalog][cat_vat_included]" value="Y"<?if($shs_SETTINGS["catalog"]["cat_vat_included"] == "Y") echo " checked"?> /></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_currency")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][currency]', $arCurrency, $shs_SETTINGS["catalog"]["currency"]?$shs_SETTINGS["catalog"]["currency"]:"RUB", "", "");?></td>
    </tr>

    <tr class="heading adittional_prices_settings">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS["adittional_currency"]) && !empty($shs_SETTINGS["adittional_currency"])){
        foreach($shs_SETTINGS["adittional_currency"] as $id => $currency){
            if(!isset($shs_SETTINGS['prices_preview'][$id])&&!isset($shs_SETTINGS['prices_detail'][$id]))
                continue;
            ?>
            <tr class="adittional_prices_settings_id_<?php echo $id;?>">
                <td>
                    <?echo GetMessage('parser_aditt_currency').' '.$arPricesNames[$id].'['.$id.']';?>
                </td>
                <td>
                    <?=SelectBoxFromArray('SETTINGS[adittional_currency]['.$id.']', $arCurrency, $currency?:"RUB", "", "");?>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>

    <?if(isset($arAllMeasure)):?>
    <tr>
        <td><?echo GetMessage("parser_measure")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][measure]', $arAllMeasure, $shs_SETTINGS["catalog"]["measure"]?$shs_SETTINGS["catalog"]["measure"]:5, "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_catalog_koef")?></td>
        <td><input type="text" name="SETTINGS[catalog][koef]" value="<?echo $shs_SETTINGS["catalog"]["koef"]?$shs_SETTINGS["catalog"]["koef"]:1;?>" size="40" maxlength="250"></td>
    </tr>
<?endif;?>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_store_catalog")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_catalog_count_default")?></td>
        <td><input type="text" name="SETTINGS[catalog][count_default]" value="<?echo $shs_SETTINGS["catalog"]["count_default"]?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_count_default_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_available_quantity")?></td>
        <td>
            <?=SelectBoxFromArray('SETTINGS[store][available_quantity]', $arAmount, isset($shs_SETTINGS["store"]["available_quantity"])?$shs_SETTINGS["store"]["available_quantity"]:'from_file', '', "");?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_count_do_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_store_list")?></td>
        <td>
            <?=SelectBoxFromArray('SETTINGS[store][list]', $arStore, isset($shs_SETTINGS["store"]["list"])?$shs_SETTINGS["store"]["list"]:GetMessage("parser_not_add_to_store"), GetMessage("parser_not_add_to_store"), "");?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_catalog_stores_descr")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_stores")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['addit_stores']) && !empty($shs_SETTINGS['addit_stores'])){
        foreach($shs_SETTINGS['addit_stores'] as $id_store => $store){
            ?>
            <tr class="adittional_store_id_<?php echo $id_store;?>">
                <td><?php echo GetMessage('parser_addit_store').$store['name'].'['.$id_store.']:';?></td>
                <td>
                    <input type="text" name="SETTINGS[addit_stores][<?php echo $id_store;?>][value]" value="<?echo $store["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="store_delete" data-store-id="<?php echo $id_store;?>">Delete</a>
                    <input type="hidden" name="SETTINGS[addit_stores][<?php echo $id_store;?>][name]" value="<?echo $store["name"];?>">
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrAdditStores', $arrAdditStores, "", GetMessage("shs_parser_addit_stores"), "");?>
            <input type="submit" id="addStore" name="refresh" value="<?=GetMessage("shs_parser_add_addit_stores")?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_addit_store_descr")?>
            <?=EndNote();?>
        </td>
    </tr>


    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_availability_list")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS['availability']['list']) && !empty($shs_SETTINGS['availability']['list'])){
        foreach($shs_SETTINGS['availability']['list'] as $i=>$av){
            ?>
            <tr data-count=<?php echo $i;?>>
                <td><?php echo GetMessage('parser_availability_row').':';?></td>
                <td>
                    <?php echo GetMessage('parser_availability_informer');?>
                    <input type='text' name='SETTINGS[availability][list][<?php echo $i;?>][text]' value="<?php echo $av['text']?>">
                    <?php echo ' - '.GetMessage('parser_availability_count');?>
                    <input type='text' name='SETTINGS[availability][list][<?php echo $i;?>][count]' value="<?php echo $av['count']?>">
                    <a href="#" class='delete_availability_row'>Delete</a>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" id="addAvailabilityRow" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_availability")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_work_price")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_convert_currency")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][convert_currency]', $arConvertCurrency, $shs_SETTINGS["catalog"]["convert_currency"], "", "");?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_price_okrug")?></td>
        <td><?=SelectBoxFromArray('SETTINGS[catalog][price_okrug]', $arPriceOkrug, $shs_SETTINGS["catalog"]["price_okrug"], "", "style=\"width:115px\"");?> <?echo GetMessage("parser_price_okrug_delta1")?> <input type="text" name="SETTINGS[catalog][price_okrug_delta]" value="<?echo $shs_SETTINGS["catalog"]["price_okrug_delta"]?>" size="1" maxlength="1"> <?echo GetMessage("parser_price_okrug_delta2")?> </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_price_okrug_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_price_format")?></td>
        <td><?echo GetMessage("parser_price_format1")?><input type="text" name="SETTINGS[catalog][price_format1]" value="<?echo $shs_SETTINGS["catalog"]["price_format1"]?>" size="1" maxlength="250"><?echo GetMessage("parser_price_format2")?><input type="text" name="SETTINGS[catalog][price_format2]" value="<?echo $shs_SETTINGS["catalog"]["price_format2"]?>" size="1" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_price_format_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?
    $count = count($shs_SETTINGS["catalog"]["price_updown"])-1;
    if(is_set($shs_SETTINGS["catalog"]["price_updown"]) && is_array($shs_SETTINGS["catalog"]["price_updown"]) && count($shs_SETTINGS["catalog"]["price_updown"])>0){
        foreach($shs_SETTINGS["catalog"]["price_updown"] as $i=>$val):
            if($count==$i) $class="tr_add";
            else $class = "";
            ?>
            <tr class="heading <?=$class?>" data-num="<?=($i+1)?>">
                <td colspan="2"><?echo GetMessage("parser_work_price_num")?> <span><?=($i+1)?></span> <?if($count==$i):?><a href="#" style="font-size:12px;" class="add_usl"><?echo GetMessage("parser_price_num_add")?></a><?endif;?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" style="font-size:12px;<?if($i==0):?>display:none<?endif;?>" class="del_usl"><?echo GetMessage("parser_price_num_del")?></a></td>
            </tr>
            <tr class="<?=$class?>">
                <td><?echo GetMessage("parser_price_updown")?></td>
                <td><?=SelectBoxFromArray('SETTINGS[catalog][price_updown][]', $arPriceUpDown, $shs_SETTINGS["catalog"]["price_updown"][$i], "", "");?></td>
            </tr>
            <tr class="<?=$class?>">
                <td><?echo GetMessage("parser_price_terms")?></td>
                <td><?=SelectBoxFromArray('SETTINGS[catalog][price_terms][]', $arPriceTerms, $shs_SETTINGS["catalog"]["price_terms"][$i], "", "");?> <?echo GetMessage("parser_price_from")?> <input type="text" name="SETTINGS[catalog][price_terms_value][]" value="<?echo $shs_SETTINGS["catalog"]["price_terms_value"][$i];?>" size="10" maxlength="250"> <?echo GetMessage("parser_price_to")?> <input type="text" name="SETTINGS[catalog][price_terms_value_to][]" value="<?echo $shs_SETTINGS["catalog"]["price_terms_value_to"][$i];?>" size="10" maxlength="250"></td>
            </tr>
            <tr class="<?=$class?>">
                <td><?echo GetMessage("parser_price_type_value")?></td>
                <td><?=SelectBoxFromArray('SETTINGS[catalog][price_type_value][]', $arPriceValue, $shs_SETTINGS["catalog"]["price_type_value"][$i], "", "");?></td>
            </tr>
            <tr class="<?=$class?> <?if($class):?>tr_last<?endif;?>">
                <td><?echo GetMessage("parser_price_value")?></td>
                <td><input type="text" name="SETTINGS[catalog][price_value][]" value="<?echo $shs_SETTINGS["catalog"]["price_value"][$i];?>" size="10" maxlength="250"></td>
            </tr>
        <?endforeach;
    }else{
        ?>
        <tr class="heading tr_add" data-num="1">
            <td colspan="2"><?echo GetMessage("parser_work_price_num")?> <span></span> <a href="#" style="font-size:12px;" class="add_usl"><?echo GetMessage("parser_price_num_add")?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" style="font-size:12px;display:none" class="del_usl"><?echo GetMessage("parser_price_num_del")?></a></td>
        </tr>
        <tr class="tr_add">
            <td><?echo GetMessage("parser_price_updown")?></td>
            <td><?=SelectBoxFromArray('SETTINGS[catalog][price_updown][]', $arPriceUpDown, $shs_SETTINGS["catalog"]["price_updown"], "", "");?></td>
        </tr>
        <tr class="tr_add">
            <td><?echo GetMessage("parser_price_terms")?></td>
            <td><?=SelectBoxFromArray('SETTINGS[catalog][price_terms][]', $arPriceTerms, $shs_SETTINGS["catalog"]["price_terms"], "", "");?> <?echo GetMessage("parser_price_from")?> <input type="text" name="SETTINGS[catalog][price_terms_value][]" value="<?echo $shs_SETTINGS["catalog"]["price_terms_value"];?>" size="10" maxlength="250"> <?echo GetMessage("parser_price_to")?> <input type="text" name="SETTINGS[catalog][price_terms_value_to][]" value="<?echo $shs_SETTINGS["catalog"]["price_terms_value_to"];?>" size="10" maxlength="250"></td>
        </tr>
        <tr class="tr_add">
            <td><?echo GetMessage("parser_price_type_value")?></td>
            <td><?=SelectBoxFromArray('SETTINGS[catalog][price_type_value][]', $arPriceValue, $shs_SETTINGS["catalog"]["price_type_value"], "", "");?></td>
        </tr>
        <tr class="tr_add tr_last">
            <td><?echo GetMessage("parser_price_value")?></td>
            <td><input type="text" name="SETTINGS[catalog][price_value][]" value="<?echo $shs_SETTINGS["catalog"]["price_value"];?>" size="10" maxlength="250"></td>
        </tr>

    <?}?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_size_selector")?></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_length")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][LENGTH]" value="<?echo $shs_SETTINGS["catalog"]["selector_product"]["LENGTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][LENGTH]" value="<?echo $shs_SETTINGS["catalog"]["selector_product_koef"]["LENGTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_width")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][WIDTH]" value="<?echo $shs_SETTINGS["catalog"]["selector_product"]["WIDTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][WIDTH]" value="<?echo $shs_SETTINGS["catalog"]["selector_product_koef"]["WIDTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_height")?></td>
        <td><input type="text" name="SETTINGS[catalog][selector_product][HEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["selector_product"]["HEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][HEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["selector_product_koef"]["HEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_size_weight")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_product][WEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["selector_product"]["WEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][selector_product_koef][WEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["selector_product_koef"]["WEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_selector_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_selector_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_selector_size_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_size_find")?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_selector_find")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][selector_find_size]" value="<?echo $shs_SETTINGS["catalog"]["selector_find_size"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_length")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][LENGTH]" value="<?echo $shs_SETTINGS["catalog"]["find_product"]["LENGTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][LENGTH]" value="<?echo $shs_SETTINGS["catalog"]["find_product_koef"]["LENGTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_width")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][WIDTH]" value="<?echo $shs_SETTINGS["catalog"]["find_product"]["WIDTH"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][WIDTH]" value="<?echo $shs_SETTINGS["catalog"]["find_product_koef"]["WIDTH"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("parser_size_height")?></td>
        <td><input type="text" name="SETTINGS[catalog][find_product][HEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["find_product"]["HEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][HEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["find_product_koef"]["HEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_size_weight")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][find_product][WEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["find_product"]["WEIGHT"];?>" size="40" maxlength="250"> X <input type="text" name="SETTINGS[catalog][find_product_koef][WEIGHT]" value="<?echo $shs_SETTINGS["catalog"]["find_product_koef"]["WEIGHT"];?>" size="10" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[catalog][catalog_delete_find_symb]" value="<?echo $shs_SETTINGS["catalog"]["catalog_delete_find_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_find_size_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
endif;
?>
<?
//********************
//�������� �����������
//********************
if(!$hideCatalog):
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_load")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[offer][load]', $arOfferLoad, $shs_SETTINGS["offer"]["load"]?$shs_SETTINGS["offer"]["load"]:1, "", "");?></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_load_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($shs_SETTINGS["offer"]["load"]) && $shs_SETTINGS["offer"]["load"]=="one"):?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_selector")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][selector]" value="<?echo $shs_SETTINGS["offer"]['one']["selector"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_selector_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_price_attr")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][price]" value="<?echo $shs_SETTINGS["offer"]['one']["price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_price_attr_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_quantity_attr")?>:</td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][quantity]" value="<?echo $shs_SETTINGS["offer"]['one']["quantity"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_quantity_attr_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_one_separator")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][one][separator]" value="<?echo $shs_SETTINGS["offer"]['one']["separator"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_one_separator_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_add_name")?></td>
        <td width="60%">
            <?if(isset($arrPropDopOfferName)):?>
                <select name="SETTINGS[offer][add_name][]" class="add_name">
                    <?foreach($arrPropDopOfferName["REFERENCE"] as $r=>$ref):?>
                        <option <?if(is_array($shs_SETTINGS["offer"]["add_name"]) && in_array($arrPropDopOfferName["REFERENCE_ID"][$r], $shs_SETTINGS["offer"]["add_name"])):?>selected=""<?endif;?> value="<?=$arrPropDopOfferName["REFERENCE_ID"][$r]?>"><?=$ref?></option>
                    <?endforeach?>
                </select>
            <?endif;?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_add_name_one_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_props_symb]" value="<?echo $shs_SETTINGS["offer"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_xml_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
<?endif;?>
    <?if(isset($shs_SETTINGS["offer"]["load"]) && $shs_SETTINGS["offer"]["load"]=="table"):?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_add_name")?></td>
        <td width="60%">
            <?if(isset($arrPropDopOfferName)):?>
                <select name="SETTINGS[offer][add_name][]" multiple="" class="add_name">
                    <?foreach($arrPropDopOfferName["REFERENCE"] as $r=>$ref):?>
                        <option <?if(is_array($shs_SETTINGS["offer"]["add_name"]) && in_array($arrPropDopOfferName["REFERENCE_ID"][$r], $shs_SETTINGS["offer"]["add_name"])):?>selected=""<?endif;?> value="<?=$arrPropDopOfferName["REFERENCE_ID"][$r]?>"><?=$ref?></option>
                    <?endforeach?>
                </select>
            <?endif;?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_add_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
    <td colspan="2"><?echo GetMessage("parser_offer_container_desc")?></td>
    </tr><?/*?>
    <tr class="heading">
        <td colspan="2">
        <?/*=BeginNote();?>
        <table>
            <thead>
                <tr>
                    <th>
                        <?echo GetMessage("parser_offer_table_desc_1")?>
                    </th>
                    <th><?echo GetMessage("parser_offer_table_desc_2")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_3")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_4")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_5")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_6")?></th>
                    <th><?echo GetMessage("parser_offer_table_desc_7")?></th>
                    <th></th>
                    <th>
                            <?echo GetMessage("parser_offer_table_desc_8")?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                <tr class="item_row">
                    <td>976886</td>
                    <td><?echo GetMessage("parser_offer_table_desc_9")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_12")?></td>
                    <td>416</td>
                    <td>8(RUS-GB-F-E-ARAB-D-H-PL)</td>
                    <td><?echo GetMessage("parser_offer_table_desc_10")?></td>
                    <td><?echo GetMessage("parser_offer_table_desc_11")?></td>
                    <td></td>
                    <td>100</td>
                </tr>
                </tbody>
            </table>
            <?=EndNote();*/?>
    <?/*?></td>
    </tr><?*/?>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_selector_item")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_item]" value="<?echo $shs_SETTINGS["offer"]["selector_item"];?>" size="40" maxlength="250"></td>
    </tr>

    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_selector_item_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_catalog_delete_symb")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][catalog_delete_selector_props_symb]" value="<?echo $shs_SETTINGS["offer"]["catalog_delete_selector_props_symb"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_delete_symb_descr_xml_offer")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_xml_name")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_name]" value="<?echo $shs_SETTINGS["offer"]["selector_name"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_name_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_price_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_price]" value="<?echo $shs_SETTINGS["offer"]["selector_price"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_price_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_more_prices")?></td>
    </tr>
    <?php
    if(isset($shs_SETTINGS["offer"]["selector_additional_prices"])&&!empty($shs_SETTINGS["offer"]["selector_additional_prices"])){
        foreach($shs_SETTINGS["offer"]["selector_additional_prices"] as $id_price => $price){
            ?>
            <tr class="offer_additional_prices_id_<?php echo $id_price;?>">
                <td><?php echo GetMessage('parser_preview_price').$price['name'].'['.$id_price.']:';?></td>
                <td>
                    <input type="text" name="SETTINGS[offer][selector_additional_prices][<?php echo $id_price;?>][value]" value="<?echo $price["value"];?>" size="40" maxlength="250">&nbsp;<a href="#" class="price_delete" data-price-id="<?php echo $id_price;?>">Delete</a>
                    <input type="hidden" name="SETTINGS[offer][selector_additional_prices][<?php echo $id_price;?>][name]" value="<?echo $price["name"];?>">
                </td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPricetypes', $arAdditPriceType, "", GetMessage("shs_parser_addit_prices"), "");?>
            <input type="submit" id="addPriceOffer" name="refresh" value="<?=GetMessage("shs_parser_add_addit_prices")?>">
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"> </td>
    </tr>

    <tr>
        <td class="field-name" width="40%"><?echo GetMessage("parser_offer_parsing_selector_quantity_xml")?></td>
        <td width="60%"><input type="text" name="SETTINGS[offer][selector_quantity]" value="<?echo $shs_SETTINGS["offer"]["selector_quantity"];?>" size="40" maxlength="250"></td>
    </tr>
    <tr>
        <td class="field-name" width="40%"></td>
        <td width="60%">
            <?=BeginNote();?>
            <?echo GetMessage("parser_offer_parsing_selector_quantity_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <?if(isset($shs_SETTINGS["offer"]["selector_prop"]) && !empty($shs_SETTINGS["offer"]["selector_prop"])):?>
        <?foreach($shs_SETTINGS["offer"]["selector_prop"] as $code=>$val):
            $val = trim($val);
            if(!$val) continue 1;
            ?>
            <tr>
                <td width="40%"><?=$arrPropDopOffer['REFERENCE_CODE_NAME'][$code]?>&nbsp;[<?=$code?>]:</td>
                <td width="60%">
                    <input type="text" size="40" data-code="<?=$code?>" name="SETTINGS[offer][selector_prop][<?=$code?>]" value="<?=$shs_SETTINGS["offer"]["selector_prop"][$code]?>">
                    <a class="prop_delete" href="#">Delete</a>
                </td>
            </tr>
        <?endforeach?>
    <?endif;?>
    <tr>
        <td colspan="2" align="center">
            <?=SelectBoxFromArray('arrPropDopOffer', $arrPropDopOffer, "", GetMessage("shs_parser_select_prop"), "");?>
            <input type="submit" id="loadDopPropOffer" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
        </td>
    </tr>
<?endif;?>

<?
endif;
?>
<?
//********************
//��� ���������
//********************
$tabControl->BeginNextTab();
?>
<?if($TYPE_OUT == 'IBLOCK'  || empty($TYPE_OUT)):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_active_element")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE_ELEMENT" value="Y"<?if($shs_ACTIVE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_code_element")?></td>
        <td width="60%"><input type="checkbox" name="CODE_ELEMENT" value="Y"<?if($shs_CODE_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_code_category")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][code_category]" value="Y"<?if($shs_SETTINGS["catalog"]["code_category"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_index_element")?></td>
        <td width="60%"><input type="checkbox" name="INDEX_ELEMENT" value="Y"<?if($shs_INDEX_ELEMENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_index_category")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][index_category]" value="Y"<?if($shs_SETTINGS["catalog"]["index_category"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_resize_image")?></td>
        <td width="60%"><input type="checkbox" name="RESIZE_IMAGE" value="Y"<?if($shs_RESIZE_IMAGE == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][img_preview_from_detail]" value="Y"<?if($shs_SETTINGS["catalog"]["img_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_preview_from_detail_text")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][text_preview_from_detail]" value="Y"<?if($shs_SETTINGS["catalog"]["text_preview_from_detail"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_date_active")?></td>
        <td width="60%"><input type="checkbox" name="DATE_ACTIVE" value="Y"<?if($shs_DATE_ACTIVE && $shs_DATE_ACTIVE != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_ACTIVE', $arrDateActive, $shs_DATE_ACTIVE, GetMessage("parser_date_type"), "id='prop-active' style='width:262px'");?></td>
    </tr>
<?/*?><tr>
        <td width="40%"><?echo GetMessage("parser_date_public")?></td>
        <td width="60%"><input type="checkbox" name="DATE_PUBLIC" value="Y"<?if($shs_DATE_PUBLIC && $shs_DATE_PUBLIC != "N") echo " checked"?>> <?=SelectBoxFromArray('DATE_PROP_PUBLIC', $arrProp, $shs_DATE_PUBLIC, GetMessage("parser_prop_id"), "id='prop-date' style='width:262px' class='prop-iblock'");?></td>
    </tr><?*/?>
<?endif;?>

    <tr>
        <td width="40%"><?echo GetMessage("parser_start_agent")?></td>
        <td width="60%"><input type="checkbox" name="START_AGENT" value="Y"<?if($shs_START_AGENT == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_start_agent_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_time_agent")?></td>
        <td width="60%"><input type="text" size="40" name="TIME_AGENT" value="<?=$shs_TIME_AGENT?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_sleep")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[catalog][sleep]" value="<?=$shs_SETTINGS["catalog"]["sleep"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_sleep_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
<?if($TYPE_OUT == 'IBLOCK'  || empty($TYPE_OUT)):?>
    <tr class="heading">
        <td colspan="2"><?php echo GetMessage('parser_proxy_header');?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_proxy").':'?></td>
        <td width="60%">
            <input type="text" size="40" name="SETTINGS[catalog][proxy]" value="<?=$shs_SETTINGS["catalog"]["proxy"]?>">
            <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][username_password]" value="<?=$shs_SETTINGS["proxy"]["username_password"]?>">
        </td>
    </tr>
<?php
if(isset($shs_SETTINGS['proxy']['servers']) && !empty($shs_SETTINGS['proxy']['servers'])){
    $i = 1;
    foreach($shs_SETTINGS['proxy']['servers'] as $id => $server){
        if(empty($server))
            continue;
        ?>
        <tr data-id="<?php echo $id?>">
            <td><?php echo GetMessage('parser_proxy').' '.$i.':';?></td>
            <td><input type="text"  size="40" name="SETTINGS[proxy][servers][<?php echo $id?>][ip]" value="<?php echo $server['ip'];?>"> <input placeholder="username:password" type="text" size="30" name="SETTINGS[proxy][servers][<?php echo $id?>][username_password]" value="<?=$server["username_password"]?>"> <a href="#" class="delete_proxy_server"><?php echo GetMessage('delete');?></a></td>
        </tr>
        <?php
        $i++;
    }
}
?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" id="addProxyServer" name="refresh" value="<? echo GetMessage('add_proxy_server')?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_proxy_username_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?
endif;
//********************
//����������
//********************
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_update")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][active]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["active"] == "Y") echo " checked"?>></td>
    </tr>
    <tr class="show_block_add_element" <?if(!isset($shs_SETTINGS["catalog"]["update"]["active"]) || ($shs_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td width="40%"><?echo GetMessage("parser_uniq_add_element")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][add_element]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["add_element"] == "Y") echo " checked"?>></td>
    </tr>
    <tr class="show_block_add_element" <?if(!isset($shs_SETTINGS["catalog"]["update"]["active"]) || ($shs_SETTINGS["catalog"]["update"]["active"] != "Y")):?>style="display: none"<?endif;?>>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_add_element_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?if($TYPE_OUT == 'IBLOCK'  || empty($TYPE_OUT)):?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][uniq][name]" value="Y"<?if($shs_SETTINGS["catalog"]["uniq"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_prop")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][uniq][prop]', $arrProp, $shs_SETTINGS["catalog"]["uniq"]["prop"], GetMessage("parser_prop_id"), "id='style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq_category")?></td>
    </tr>

    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_dop_fields_category")?></td>
        <td width="60%"><?=SelectBoxFromArray('SETTINGS[catalog][uniq_category][dop_fields_category]', $arrUniqCategory, $shs_SETTINGS["catalog"]["uniq_category"]["dop_fields_category"], GetMessage("parser_select_uniq_category"), "id='style='width:262px' class='prop-iblock'");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_uniq_category_descr_xml")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq_field")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_name")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][name]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_price")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][price]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_count")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][count]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_param")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][param]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["param"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_descr]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["preview_descr"], "", "");?>
            <?/*?><input type="checkbox" name="SETTINGS[catalog][update][preview_descr]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["preview_descr"] == "Y") echo " checked"?>><?*/?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_descr")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_descr]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["detail_descr"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_preview_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][preview_img]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["preview_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_detail_img")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][update][detail_img]', $arUpdate, $shs_SETTINGS["catalog"]["update"]["detail_img"], "", "");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_more_img")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][more_img]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["more_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_props")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][props]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_category_descr")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][descr_category]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["descr_category"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_category_pic")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][pic_category]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["pic_category"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_uniq_field_category_field")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][update][field_category]" value="Y"<?if($shs_SETTINGS["catalog"]["update"]["field_category"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_uniq_field_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_element_action")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_element_action")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[catalog][uniq][action]', $arAction, $shs_SETTINGS["catalog"]["uniq"]["action"], "", "");?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_element_action_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?endif;?>
<?if($TYPE_OUT == 'HL'):?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_header_uniq")?></td>
    </tr>
<?if(!empty($shs_SETTINGS["catalog"]["uniq"]["fields"]) && is_array($shs_SETTINGS["catalog"]["uniq"]["fields"])):?>
    <?foreach($shs_SETTINGS["catalog"]["uniq"]["fields"] as $column):?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?=$column?>:</td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input class="arrFieldsDefaultInput" type="hidden" name="SETTINGS[catalog][uniq][fields][]" value="<?=$column?>">
                <a class="delete_default_field_hl" href="#"><?=GetMessage("parser_caption_detete_button");?></a>
            </td>
        </tr>
    <?endforeach;?>
<?endif;?>

    <tr>
        <td width="40%"><?echo GetMessage("parser_uniq_prop")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('arrFieldsDefault', $arSelectColumns, '', GetMessage("parser_fields_id"), "id='style='width:262px' class='prop-iblock'");?>
            <?//SETTINGS[catalog][uniq][fields]?>
            <input type="submit" id="loadFieldDefault" name="refresh" value="<?=GetMessage("shs_parser_select_prop_but")?>">
            <a class="delete_default_field_hl" href="#"><?=GetMessage("parser_caption_detete_button");?></a>
        </td>
    </tr>
<?endif;
//********************
//�����������
//********************
if($TYPE_OUT == 'IBLOCK' || empty($TYPE_OUT)):
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_type")?></td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[xml][auth][type]', $arAuthType, $shs_SETTINGS["xml"]["auth"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_active")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[xml][auth][active]" value="Y"<?if($shs_SETTINGS["xml"]["auth"]["active"] == "Y") echo " checked"?>></td>
    </tr>
<?if((isset($shs_SETTINGS["xml"]["auth"]["type"]) && $shs_SETTINGS["xml"]["auth"]["type"]=="form") || !isset($shs_SETTINGS["xml"]["auth"]["type"])):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_url")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][url]" value="<?=$shs_SETTINGS["xml"]["auth"]["url"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_url_xml_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_selector")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][selector]" value="<?=$shs_SETTINGS["xml"]["auth"]["selector"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][login]" value="<?=$shs_SETTINGS["xml"]["auth"]["login"]?>"> <?echo GetMessage("parser_auth_login_name")?> <input type="text" size="20" name="SETTINGS[xml][auth][login_name]" value="<?=$shs_SETTINGS["xml"]["auth"]["login_name"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][password]" value="<?=$shs_SETTINGS["xml"]["auth"]["password"]?>"> <?echo GetMessage("parser_auth_password_name")?> <input type="text" size="20" name="SETTINGS[xml][auth][password_name]" value="<?=$shs_SETTINGS["xml"]["auth"]["password_name"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_auth_password_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?else:?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_login")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][login]" value="<?=$shs_SETTINGS["xml"]["auth"]["login"]?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_auth_password")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[xml][auth][password]" value="<?=$shs_SETTINGS["xml"]["auth"]["password"]?>"></td>
    </tr>
<?endif;?>
<?if($shs_SETTINGS["xml"]["auth"]["type"]=="form"):?>
    <tr>
        <td width="40%"></td>
        <td width="60%"><input type="button" size="40" id="auth" name="auth" data-href="<?=$APPLICATION->GetCurPageParam("auth=1", array("auth")); ?>" value="<?echo GetMessage('parser_auth_check')?>"></td>
    </tr>
<?endif;?>
<?

//********************
//����
//********************
$tabControl->BeginNextTab();
?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[catalog][log]" value="Y"<?if($shs_SETTINGS["catalog"]["log"] == "Y") echo " checked"?>></td>
    </tr>
<?
$file_log = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/shs.parser/include/catalog_log_".htmlspecialcharsbx($_GET["ID"]).".txt";
if(isset($_GET["ID"]) && file_exists($file_log)):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_header_logs_download")?></td>
        <td width="60%"><a target="_blank" href="<?=BX_ROOT.'/admin/fileman_file_view.php?path='.urlencode(BX_ROOT."/modules/shs.parser/include/catalog_log_".htmlspecialcharsbx($_GET["ID"]).".txt")?>">catalog_log_<?=htmlspecialcharsbx($_GET["ID"])?>.txt  (<?=htmlspecialcharsbx(ceil(filesize($file_log)/1024))?> KB)</a></td>
    </tr>
<?endif?>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_header_log_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_smart_logs_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_smart_logs")?></td>
        <td width="60%"><input type="checkbox" name="SETTINGS[smart_log][enabled]" value="Y"<?if($shs_SETTINGS["smart_log"]["enabled"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_iteration")?></td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[smart_log][iteration]" value="<?=$shs_SETTINGS["smart_log"]["iteration"]!=''?$shs_SETTINGS["smart_log"]["iteration"]:5?>"></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_props")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_props]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_props"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_price")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_price]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_price"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_count")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_count]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_count"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_descr]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_descr")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_descr]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_prev_descr"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_prev_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_prev_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_prev_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_addit_img")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_addit_img]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_addit_img"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_log_save_set_catalog")?></td>
        <td width="60%"><input type="checkbox" size="40" name="SETTINGS[smart_log][settings][save_set_catalog]" value="Y"<?if($shs_SETTINGS["smart_log"]["settings"]["save_set_catalog"] == "Y") echo " checked"?>></td>
    </tr>
<?
//********************
//�������
//********************
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_type_head")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_type")?>:</td>
        <td width="60%">
            <?=SelectBoxFromArray('SETTINGS[loc][type]', $arLocType, $shs_SETTINGS["loc"]["type"], "", "class='select_load'");?>
        </td>
    </tr>
<?if(isset($shs_SETTINGS["loc"]["type"]) && $shs_SETTINGS["loc"]["type"]=="yandex"):?>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_key")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[loc][yandex][key]" value="<?=$shs_SETTINGS["loc"]["yandex"]["key"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_key_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_yandex_lang")?>:</td>
        <td width="60%"><input type="text" size="20" name="SETTINGS[loc][yandex][lang]" value="<?=$shs_SETTINGS["loc"]["yandex"]["lang"]?>"></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_yandex_lang_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_fields")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_name")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_name]" value="Y"<?if($shs_SETTINGS["loc"]["f_name"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_preview_text")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_preview_text]" value="Y"<?if($shs_SETTINGS["loc"]["f_preview_text"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_detail_text")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_detail_text]" value="Y"<?if($shs_SETTINGS["loc"]["f_detail_text"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_loc_fields_props")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[loc][f_props]" value="Y"<?if($shs_SETTINGS["loc"]["f_props"] == "Y") echo " checked"?>></td>
    </tr>
<?endif;?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_loc_uniq")?></td>
    </tr>
<?
$engine = new Engine\Yandex();
$arSettings = $engine->getSettings();
$arDomains = \CSeoUtils::getDomainsList();

foreach($arDomains as $key => $domain)
{
    if(!isset($arSettings['SITES'][$domain['DOMAIN']]))
    {
        unset($arDomains[$key]);
    }
}

if(count($arDomains) <= 0)
{
    $msg = new CAdminMessage(array(
        'MESSAGE' => Loc::getMessage('SHS_PARSER_SEO_YANDEX_ERROR'),
        'HTML' => 'Y'
    ));
}else{
    $arrDomain['REFERENCE'][] =  Loc::getMessage('shs_parser_loc_uniq_no');
    $arrDomain['REFERENCE_ID'][] = "";
    foreach($arDomains as $domain)
    {   //printr($domain);
        $domainEnc = Converter::getHtmlConverter()->encode($domain['DOMAIN']);
        $arrDomain['REFERENCE'][] =  $domainEnc;
        $arrDomain['REFERENCE_ID'][] = $domainEnc;
    }
}
?>
<?if(count($arDomains) <= 0):?>
    <tr>
        <td colspan="2" align="center"><?echo $msg->Show();?></td>
    </tr>
<?else:?>
    <tr>
        <td><?echo GetMessage("parser_loc_uniq_domain")?>:</td>
        <td><?=SelectBoxFromArray('SETTINGS[loc][uniq][domain]', $arrDomain, $shs_SETTINGS["loc"]["uniq"]["domain"], "", "");?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=BeginNote();?>
            <?echo GetMessage("parser_loc_uniq_domain_descr")?>
            <?=EndNote();?>
        </td>
    </tr>
<?endif?>
<?
endif;
//********************
//Видео уроки
//********************
$tabControl->BeginNextTab();
?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_video_xml_descr")?></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%"><iframe width="800" height="500" src="https://www.youtube.com/embed/oP8tT_n55BI" frameborder="0" allowfullscreen></iframe></td>
    </tr>
    <tr>
        <td align="center" colspan="2" width="100%"><iframe width="800" height="500" src="https://www.youtube.com/embed/HrnkiR-msnQ" frameborder="0" allowfullscreen></iframe></td>
    </tr>
<?
if(isset($_GET["log_ID"]) && isset($_GET["ID"])):
    if (ob_get_level()) {
        ob_end_clean();
    }
    $file = $file_log;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit();
endif;
//********************
//Email-����������
//********************
$tabControl->BeginNextTab();  ?>
    <tr class="heading">
        <td colspan="2"><?echo GetMessage("parser_notification")?></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_start")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][start]" value="Y"<?if($shs_SETTINGS["notification"]["start"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_end")?>:</td>
        <td width="60%"><input type="checkbox" name="SETTINGS[notification][end]" value="Y"<?if($shs_SETTINGS["notification"]["end"] == "Y") echo " checked"?>></td>
    </tr>
    <tr>
        <td width="40%"><?echo GetMessage("parser_notification_email")?>:</td>
        <td width="60%"><input type="text" size="40" name="SETTINGS[notification][email]" value="<?=!empty($shs_SETTINGS["notification"]["email"])?$shs_SETTINGS["notification"]["email"]:COption::GetOptionString("main", "email_from")?>"></td>
    </tr>
<?php
$tabControl->Buttons(
    array(
        "disabled"=>($POST_RIGHT<"W"),
        "back_url"=>"list_parser_admin.php?lang=".LANG,
    )
);
?>
<?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
    <input type="hidden" name="ID" value="<?=htmlspecialcharsbx($ID)?>">
<?endif;?>
    <input type="hidden" name="parent" value="<?=htmlspecialcharsbx($parentID)?>">
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>