<?php
class FileGetHtml{
    public $headerUrl = "";
    public $httpCode = "";
    function file_get_html($path, $proxy=false, $auth=false, $this_=false) {
        global $kit_ID;
        //убираем якорь если он есть. т.к. выдает ошибку 404 если урл с якорем (хотя в документации curl используется урл с якорем)
        $path = str_replace(strrchr($path, "#"), '', $path);

        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:14.0) Gecko/20100101 Firefox/14.0.1');
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        
        if($auth && $kit_ID)
        {
            if(isset($this_->settings[$this_->typeN]["auth"]["type"]) && $this_->settings[$this_->typeN]["auth"]["type"]=="http")
            {
                $user = $this_->settings[$this_->typeN]["auth"]["login"];
                $pass = $this_->settings[$this_->typeN]["auth"]["password"];
                curl_setopt($ch, CURLOPT_USERPWD, $user.":".$pass);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
                curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
            }
            else{
                curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
                curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
            }
            
        }
        elseif($kit_ID)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/loc".$kit_ID.".txt");
            curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/loc".$kit_ID.".txt");
        }

        if($proxy && !empty($proxy))
        {
            $p = $this->getRandomProxy($proxy);
            curl_setopt($ch, CURLOPT_PROXY, $p['ip']);
            
            if(!empty($p['username_password'])){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $p['username_password']);
            }
        }
        
        $data = curl_exec($ch);

        $this->headerUrl  = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL);
        $this->httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $this->effectivUrl = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);
        return $data;
    }
    
    /*
    * Return random not empty proxy from array of proxy
    *
    * param proxyArray - array of proxy
    */
    function getRandomProxy($proxyArray = null)
    {
        $i = rand(0, count($proxyArray)-1);

        if(!empty($proxyArray) && $proxyArray[$i]['ip']!='')
        {
            return $proxyArray[$i];
        }
        else
        {
            unset($proxyArray[$i]);
            return $this->getRandomProxy($proxyArray);
        }
    }

    function file_get_image($path, $proxy=false, $auth=false, $this_=false, $filename)
    {
        global $kit_ID;
        $fp = fopen ($filename, 'w');
        $ch = curl_init($path);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:14.0) Gecko/20100101 Firefox/14.0.1');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);// �� ��������� SSL ����������
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);    // �� ��������� Host SSL �����������
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);       // ������� ������
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        if($auth && $kit_ID)
        {
            if(isset($this_->settings[$this_->typeN]["auth"]["type"]) && $this_->settings[$this_->typeN]["auth"]["type"]=="http")
            {
                $user = $this_->settings[$this_->typeN]["auth"]["login"];
                $pass = $this_->settings[$this_->typeN]["auth"]["password"];
                curl_setopt($ch, CURLOPT_USERPWD, $user.":".$pass);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
                curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
            }else{
                curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
                curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt");
            }

        }
        elseif($kit_ID)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/loc".$kit_ID.".txt");
            curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/loc".$kit_ID.".txt");
        }

        if($proxy && !empty($proxy))
        {
            $p = $this->getRandomProxy($proxy);
            curl_setopt($ch, CURLOPT_PROXY, $p['ip']);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            if(!empty($p['username_password']))
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $p['username_password']);
        }

        curl_exec($ch); //print $data;
        $this->headerUrl  = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL);
        $this->httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close($ch);
        fclose($fp);

        return $filename;
    }
 
    function file_get_local_html($path)
    {
        $path = str_replace(substr($_SERVER['DOCUMENT_ROOT'], 1), '', $path);
        $data = file_get_contents($_SERVER['DOCUMENT_ROOT']."/".$path);
        
        if(strlen($data) > 0)
            $this->httpCode = 200;
        else
            $this->httpCode = 404;

        return $data;
    }

    function auth($path, $proxy=false, $postdata, $check=false)
    {
        global $kit_ID;
        $coo = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kit.parser/include/coo".$kit_ID.".txt";

        if($check && file_exists($coo))
            unlink($coo);
       
        $ch = curl_init( $path );
        $strPost = "";
        $count = 0;

        foreach($postdata as $i=>$v)
        {
            $count++;

            if(empty($i))
                continue;

            $strPost .= $i."=".$v;

            if($count!=count($postdata))
                $strPost .="&";
        }
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);// �� ��������� SSL ����������
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);    // �� ��������� Host SSL �����������
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36');
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $coo);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $coo);

        if($proxy && !empty($proxy))
        {
            $p = $this->getRandomProxy($proxy);
            curl_setopt($ch, CURLOPT_PROXY, $p['ip']);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            if(!empty($p['username_password']))
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $p['username_password']);
        }
        
        $data = curl_exec($ch);

        $this->headerUrl  = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL);
        $this->httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        
        return $data;
    }
}
?>