<?
IncludeModuleLangFile(__FILE__);
Class KitParserContentGeneral{
    function CheckFields($arFields)
    {
        global $DB;
        $this->LAST_ERROR = "";
        $aMsg = array();

        if(array_key_exists("NAME", $arFields))
        {
            if(strlen($arFields["NAME"])<=0)
                $aMsg[] = array("id"=>"NAME", "text"=>GetMessage("class_parser_err_name"));
        }

        if(array_key_exists("RSS", $arFields))
        {
            if(strlen($arFields["RSS"])<=0)
                $aMsg[] = array("id"=>"RSS", "text"=>GetMessage("class_parser_err_rss"));
        }

        if(array_key_exists("SORT", $arFields))
        {
            if(!preg_match('/^\d+$/', $arFields['SORT']))
                $aMsg[] = array("id"=>"SORT", "text"=>GetMessage("class_parser_err_sort"));
        }

        if(array_key_exists("IBLOCK_ID", $arFields))
        {
            if(strlen($arFields["IBLOCK_ID"])<=0)
                $aMsg[] = array("id"=>"IBLOCK_ID", "text"=>GetMessage("class_parser_err_iblock"));
        }

        if(array_key_exists("TIME_AGENT", $arFields))
        {
            if(($arFields['START_AGENT']=="Y" && !empty($arFields["TIME_AGENT"]) && preg_match('/\D/', $arFields["TIME_AGENT"])) || ($arFields['START_AGENT']=="Y" && empty($arFields["TIME_AGENT"])))
                $aMsg[] = array("id"=>"IBLOCK_ID", "text"=>GetMessage("class_parser_err_time_agent"));
        }
        if(array_key_exists("SETTINGS", $arFields))
        {
            $arFields["SETTINGS"] = htmlspecialcharsEx($arFields["SETTINGS"]);
        }

        if(!empty($aMsg))
        {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }

        return true;
    }

    function Update($ID, $arFields){
        global $DB, $USER;
        $ID = intval($ID);
        //print_r($arFields);

        if(!$this->CheckFields($arFields))
            return false;

        $strUpdate = $DB->PrepareUpdate("b_kit_parser", $arFields);

        if($strUpdate!="")
        {
            $strSql = "UPDATE b_kit_parser SET ".$strUpdate." WHERE ID=".$ID;
            $arBinds = array();

            if(!$DB->QueryBind($strSql, $arBinds))
                return false;
        }

        return true;
    }

    function Add($arFields)
    {
        global $DB;

        if(!$this->CheckFields($arFields))
            return false;

        $ID = $DB->Add("b_kit_parser", $arFields);

        return $ID;
    }

    function Delete($ID)
    {
        global $DB;
        CModule::IncludeModule("main");
        $ID = intval($ID);
        $arAgent = CAgent::GetList(array(), array("NAME"=>"CKitParser::startAgent(".$ID.");"))->Fetch();
        CAgent::Delete($arAgent["ID"]);
        $DB->StartTransaction();
        $res = $DB->Query("DELETE FROM b_kit_parser WHERE ID='".$ID."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if($res)
            $DB->Commit();
        else
            $DB->Rollback();

        return $res;
    }

    function GetByID($ID)
    {
        global $DB;
        $ID = intval($ID);
        $strSql = "SELECT P.* FROM b_kit_parser P WHERE P.ID = '".$ID."'";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    function getListShort()
    {
        global $DB;

        $strSql = "SELECT P.ID, P.NAME FROM b_kit_parser P ORDER BY ID DESC";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
}
?>