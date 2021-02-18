<?php
//  Title: Google Base Data Feeder
//  Last Update: 18/12/20 by Ismail sana


/*************** BEGIN MASTER SETTINGS ******************/

define('SEO_ENABLED','true');    //Change to 'false' to disable if Ultimate SEO URLs is not installed
define('FEEDNAME', 'bla.txt');       //from your googlebase account
define('DOMAIN_NAME', ''); //your correct domain name (don't include www unless it is used)
define('FTP_USERNAME', 'googleFTP-username'); //created from within your googlebase account
define('FTP_PASSWORD', 'googleFTP-password'); //created from within your googlebase account
define('FTP_ENABLED', (isset($_GET['noftp']) ? '0' : '0'));      //set to 0 to disable
define('CONVERT_CURRENCY', '0'); //set to 0 to disable - only needed if a feed in a difference currecny is required
define('CURRENCY_TYPE', 'EUR');  //(eg. USD, EUR, GBP)
define('DEFAULT_LANGUAGE', 1);   //Change this to the id of your language.  BY default 1 is english
define('QUOTES_CATEGORY_NAME',''); //if the Quotes contribution is installed, enter the name of the quotes category here
define('OPTIONS_ENABLED', 1);
define('OPTIONS_ENABLED_LABEL', 0);
define('OPTIONS_ENABLED_IDENTIFIER_EXISTS', 1);
define('OPTIONS_ENABLED_AGE_RANGE', 0);
define('OPTIONS_ENABLED_ATTRIBUTES', 0);
define('OPTIONS_ENABLED_BRAND', 1);
define('OPTIONS_ENABLED_CONDITION', 1);
define('OPTIONS_ENABLED_CURRENCY', 0);
define('OPTIONS_ENABLED_EXPIRATION', 1);
define('OPTIONS_ENABLED_FEED_LANGUAGE', 1);
define('OPTIONS_ENABLED_FEED_QUANTITY', 0);
define('OPTIONS_ENABLED_GTIN', 0);
define('OPTIONS_ENABLED_GOOGLE_UTM', 0);
define('OPTIONS_ENABLED_ISBN', 0);
define('OPTIONS_ENABLED_MADE_IN', 0);
define('OPTIONS_ENABLED_MANUFACTURER', 0);         //displays the manufacturer name
define('OPTIONS_ENABLED_PAYMENT_ACCEPTED', 0);
define('OPTIONS_ENABLED_PRODUCT_MODEL', 1); //displays the product model
define('OPTIONS_ENABLED_PRODUCT_TYPE', 1);
define('OPTIONS_ENABLED_SHIPPING', 0);
define('OPTIONS_ENABLED_INCLUDE_TAX', 0);
define('OPTIONS_ENABLED_UPC', 0);
define('OPTIONS_MPN', 'model');  
define('OPTIONS_LABEL', 'Test');
define('OPTIONS_ENABLED_WEIGHT', 0);
define('OPTIONS_AVAILABILITY', 'preorder');

//the following only matter if the matching option is enabled above.
define('OPTIONS_AGE_RANGE', '16-90 years');
define('OPTIONS_BRAND', 'name');
define('OPTIONS_CONDITION', 'New');  //possible entries are New, Refurbished, Used
define('OPTIONS_DEFAULT_CURRENCY', 'EUR');
define('OPTIONS_DEFAULT_FEED_LANGUAGE', 'en');
define('OPTIONS_DEFAULT_GOOGLE_UTM', ''); //see http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&answer=55578
define('OPTIONS_GTIN', '');
define('OPTIONS_ISBN', '');
define('OPTIONS_MADE_IN', 'IT');
define('OPTIONS_MANUFACTURERS_NAME_IGNORE', ''); //list if comma separated manufacturer names to be skipped - e.g. Matrox,Fox
define('OPTIONS_PAYMENT_ACCEPTED_METHODS', '');  //Acceptable values: Cash, Check, GoogleCheckout, Visa, MasterCard, AmericanExpress, Discover, wiretransfer
define('OPTIONS_PRODUCT_TYPE', 'full'); //full means the full category path (i.e., hardware,printers), anything else, or blank, means just the products category (i.e., printers)

//the following is for the shipping override option - enter multiple values separated by a comma
//Format entries follow. A colon must be present for each field, whether it is entered or not.
// COUNTRY - OPTIONAL - If country isn't included, we'll assume the shipping price applies to the target country of the item. If region isn't included, the shipping price will apply across the entire country.
// REGION  - OPTIONAL - blank for entire country, otherwise, us two-letter State (CA), full zip code (90210) or wildcard zip code (902*)
// SERVICE - OPTIONAL - The service class or delivery speed, i.e. ground
// PRICE   - REQUIRED - Fixed shipping price (assumes the same currency as the price attribute)
define('OPTIONS_SHIPPING_STRING','IT::Standard:2.95,IT::Standard:10.00'); //says charge tax to US for residents of Florida at 5% and don't apply tax to shipping

//the following is for the tax override option - enter multiple values separated by a comma
//Format entries follow. A colon must be present for each field, whether it is entered or not.
// COUNTRY  - OPTIONAL - country the tax applies to - only US for now
// REGION   - OPTIONAL - blank for entire country, otherwise, us two-letter State (CA), full zip code (90210) or wildcard zip code (902*)
// TAX      - REQUIRED - default = 0 (e.g. for 5.76% tax use 5.76)
// SHIPPING - OPTIONAL - do you charge tax on shipping - choices are y or n
//define('OPTIONS_TAX_STRING', 'IT::21.00:Y'); //says charge tax to US for residents of Florida at 5% and don't apply tax to shipping

define('OPTIONS_UPC', '');
define('OPTIONS_WEIGHT_ACCEPTED_METHODS', 'kg'); //Valid units include lb, pound, oz, ounce, g, gram, kg, kilogram.

//the following allow skipping certain items
define('OPTIONS_IGNORE_PRODUCT_ZERO', 0);  //0 = include products with qty of 0 in output, 1 = ignore products with qty of 0

/*************** END MASTER SETTINGS ******************/


/*************** NO EDITS NEEDED BELOW THIS LINE *****************/

require_once('../includes/configure.php');

if (! function_exists("tep_not_null")) {
   function tep_not_null($value) {
      if (is_array($value)) {
         return ((sizeof($value) > 0) ? true : false);
      } else {
         return  ((($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) ? true : false);
      }
   }
}

if(SEO_ENABLED=='true'){
  //********************
  // Modification for SEO
  // Since the ultimate SEO was only installed on the public side, we will include our files from there.
  require_once('../includes/filenames.php');
  require_once('../includes/database_tables.php');

  include_once('../' .DIR_WS_CLASSES . 'seo.class.php');
  $seo_urls = new SEO_URL(DEFAULT_LANGUAGE);

  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
     global $seo_urls;
     return $seo_urls->href_link($page, $parameters, $connection, $add_session_id);
  }
}

//********************
//  Start TIMER
//  -----------
$stimer = explode( ' ', microtime() );
$stimer = $stimer[1] + $stimer[0];
//  -----------


$OutFile = "../feeds/" . FEEDNAME;
$destination_file = FEEDNAME;
$source_file = $OutFile;
$imageURL = 'http://' . DOMAIN_NAME . '/images/';
if(SEO_ENABLED=='true'){
   $productURL = 'product_info.php'; // ***** Revised for SEO
   $productParam = "products_id=";   // ***** Added for SEO
}else{
   $productURL = 'http://' . DOMAIN_NAME . '/product_info.php?products_id=';
}

$already_sent = array();

if(CONVERT_CURRENCY)
{
   if(SEO_ENABLED=='true'){
       $productParam="currency=" . CURRENCY_TYPE . "&products_id=";
   }else{
       $productURL = "http://" . DOMAIN_NAME . "/product_info.php?currency=" . CURRENCY_TYPE . "&products_id=";  //where CURRENCY_TYPE is your currency type (eg. USD, EUR, GBP)
   }
}

$feed_exp_date = date('Y-m-d', time() + 2419200 );

if (!($link=mysql_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD))) {
    echo "Error when connecting itself to the data base";
    exit();
}
if (!mysql_select_db( DB_DATABASE , $link )) {
   echo "Error the data base does not exist";
   exit();
}

$quotes = '';


if (QUOTES_CATEGORY_NAME !== '') {
   $quotes = " and products.customers_email_address = '' and products.quotes_email_address = ''";
}

    $extraFields = '';

  if (OPTIONS_AVAILABILITY == '') {
   $extraFields .= ' products_description.products_availability as availability, ';
}

    if (OPTIONS_ENABLED_BRAND == 1 && strlen(OPTIONS_BRAND) == 0) { //brand is enabled but not set so load from database
     
        $extraFields .= ' manufacturers.manufacturers_temp, ';
      
    }

    if (OPTIONS_ENABLED_GTIN == 1 && strlen(OPTIONS_GTIN) == 0) {
      
        $extraFields .= ' products.products_gtin as gtin, ';
    }
    if (OPTIONS_ENABLED_ISBN == 1 && strlen(OPTIONS_ISBN) == 0) {
        $extraFields .= ' products.products_isbn as isbn, ';
    }
    if (OPTIONS_ENABLED_UPC == 1 && strlen(OPTIONS_UPC) == 0) {
        $extraFields .= ' products.products_upc as upc, ';
    }

    $sql = "
SELECT concat( '" . $productURL . "' ,products.products_id) AS product_url,
products_model AS prodModel,
manufacturers.manufacturers_name AS mfgName,
manufacturers.manufacturers_id,
products.products_id AS id,
products_description.products_name AS name,
LEFT(products_description.products_description, 3344) AS description,
products.products_quantity AS quantity,
products.products_status AS prodStatus,
products.products_weight AS prodWeight, " . $extraFields . "
FORMAT( IFNULL(specials.specials_new_products_price, products.products_price), 2) AS price,
CONCAT( '" . $imageURL . "' ,products.products_image) AS image_url,
products_to_categories.categories_id AS prodCatID,
categories.parent_id AS catParentID,
categories_description.categories_name AS catName
FROM (categories,
categories_description,
products,
" . $img_array['table'] . "
products_description,
products_to_categories)

left join manufacturers on ( manufacturers.manufacturers_id = products.manufacturers_id )
left join specials on ( specials.products_id = products.products_id AND ( ( (specials.expires_date > CURRENT_DATE) OR (specials.expires_date is NULL) OR (specials.expires_date = 0) ) AND ( specials.status = 1 ) ) )

WHERE products.products_id=products_description.products_id
" . $img_array['and'] . "
AND products.products_id=products_to_categories.products_id
AND products_to_categories.categories_id=categories.categories_id
AND categories.categories_id=categories_description.categories_id " . $quotes . "
AND categories_description.language_id = " . DEFAULT_LANGUAGE . "
AND products_description.language_id = " . DEFAULT_LANGUAGE . "
ORDER BY
products.products_id ASC
";


    $quotes = '';
    if (QUOTES_CATEGORY_NAME !== '') {
        $quotes = " and categories_description.categories_name NOT LIKE '" . QUOTES_CATEGORY_NAME . "' ";
    }

    $catInfo = "
SELECT
categories.categories_id AS curCatID,
categories.parent_id AS parentCatID,
categories_description.categories_name AS catName
FROM
categories,
categories_description
WHERE categories.categories_id = categories_description.categories_id " . $quotes . "
AND categories_description.language_id = " . DEFAULT_LANGUAGE . "";

    function findCat($curID, $catTempPar, $catTempDes, $catIndex)
    {
        if ((isset($catTempPar[$curID])) && ($catTempPar[$curID] != 0)) {
            if (isset($catIndex[$catTempPar[$curID]])) {
                $temp = $catIndex[$catTempPar[$curID]];
            } else {
                $catIndex = findCat($catTempPar[$curID], $catTempPar, $catTempDes, $catIndex);
                $temp = $catIndex[$catTempPar[$curID]];
            }
        }
        if ((isset($catTempPar[$curID])) && (isset($catTempDes[$curID])) && ($catTempPar[$curID] == 0)) {
            $catIndex[$curID] = $catTempDes[$curID];
        } else {
            $catIndex[$curID] = $temp . ", " . $catTempDes[$curID];
        }
        return $catIndex;
    }

    $catIndex = array();
    $catTempDes = array();
    $catTempPar = array();
    $processCat = mysql_query($catInfo) or die($FunctionName . ": SQL error " . mysql_error() . "| catInfo = " . htmlentities($catInfo));

    while ($catRow = mysql_fetch_object($processCat)) {
        $catKey = $catRow->curCatID;
        $catName = $catRow->catName;
        $catParID = $catRow->parentCatID;
        if ($catName != "") {
            $catTempDes[$catKey] = $catName;
            $catTempPar[$catKey] = $catParID;
        }
    }

 

    foreach ($catTempDes as $curID => $des) { //don't need the $des
        $catIndex = findCat($curID, $catTempPar, $catTempDes, $catIndex);
    }

    $_strip_search = array(
        "![\t ]+$|^[\t ]+!m", // remove leading/trailing space chars
        '%[\r\n]+%m'); // remove CRs and newlines
    $_strip_replace = array(
        '',
        ' ');
    $_cleaner_array = array(">" => "> ", "&reg;" => "", "�" => "", "&trade;" => "", "�" => "", "\t" => "", "	" => "", "&quot;" => "\"");




    if (file_exists($OutFile)) {
        unlink($OutFile);
    }

    $output = "link\ttitle\tdescription\tprice\timage_link\tid\tavailability";
    $attributesColumns = array();

//create optional section
    if (OPTIONS_ENABLED == 1) {

    
        if (OPTIONS_ENABLED_AGE_RANGE == 1) $output .= "\tage_range";
        if (OPTIONS_ENABLED_LABEL == 1) $output .= "\tadwords_labels";
        if (OPTIONS_ENABLED_BRAND == 1) $output .= "\tbrand";
        if (OPTIONS_ENABLED_CONDITION == 1) $output .= "\tcondition";
        if (OPTIONS_ENABLED_CURRENCY == 1) $output .= "\tcurrency";
        if (OPTIONS_ENABLED_EXPIRATION == 1) $output .= "\texpiration_date";
        if (OPTIONS_ENABLED_FEED_LANGUAGE == 1) $output .= "\tlanguage";
        if (OPTIONS_ENABLED_FEED_QUANTITY == 1) $output .= "\tquantity";
        if (OPTIONS_ENABLED_GTIN == 1) $output .= "\tgtin";
        if (OPTIONS_ENABLED_ISBN == 1) $output .= "\tisbn";
        if (OPTIONS_ENABLED_MADE_IN == 1) $output .= "\tmade_in";
        if (OPTIONS_ENABLED_MANUFACTURER == 1) $output .= "\tmanufacturer";
        if (OPTIONS_ENABLED_PAYMENT_ACCEPTED == 1) $output .= "\tpayment_accepted";
        if (OPTIONS_ENABLED_PRODUCT_MODEL == 1) $output .= "\tmpn";
        if (OPTIONS_ENABLED_PRODUCT_TYPE == 1) $output .= "\tproduct_type";
        if (OPTIONS_ENABLED_SHIPPING == 1) $output .= "\tshipping";
        if (OPTIONS_ENABLED_INCLUDE_TAX == 1) $output .= "\ttax";
        if (OPTIONS_ENABLED_UPC == 1) $output .= "\tupc";
        if (OPTIONS_ENABLED_WEIGHT == 1) $output .= "\tweight";
        if (OPTIONS_ENABLED_IDENTIFIER_EXISTS == 1) $output .= "\tidentifier_exists";

        if (OPTIONS_ENABLED_ATTRIBUTES == 1) {
            $products_options_name_query = mysql_query("select distinct popt.products_options_id, popt.products_options_name from products_options popt, products_attributes patrib where popt.language_id = '" . (int)1 . "' order by popt.products_options_name") or die(mysql_error());
            while ($products_options_name = mysql_fetch_object($products_options_name_query)) {
                $attributesColumns[] = $products_options_name->products_options_name;
                $name = strtolower($products_options_name->products_options_name);
                $name = str_replace(" ", "_", $name);
                $output .= "\tc:" . $name;
            }

        }
    }
    $output .= " \n";


    $result = mysql_query($sql) or die($FunctionName . ": SQL error " . mysql_error() . "| sql = " . htmlentities($sql));

//Currency Information
    if (CONVERT_CURRENCY) {
        $sql3 = "
   SELECT
   currencies.value AS curUSD
   FROM
   currencies
   WHERE currencies.code = '" . CURRENCY_TYPE . "'";

        $result3 = mysql_query($sql3) or die($FunctionName . ": SQL error " . mysql_error() . "| sql3 = " . htmlentities($sql3));
        $row3 = mysql_fetch_object($result3);
    }

    $loop_counter = 0;

    while ($row = mysql_fetch_object($result)) {
        if (OPTIONS_IGNORE_PRODUCT_ZERO > 0 && $row->quantity < 1) continue; //skip products with 0 qty
        if (isset($already_sent[$row->id])) continue; // if we've sent this one, skip the rest of the while loop

        if ($row->prodStatus == 1) {
            if (CONVERT_CURRENCY) {
                $row->price = preg_replace("/[^.0-9]/", "", $row->price);
                $row->price = $row->price * $row3->curUSD;
                $row->price = number_format($row->price, 2, '.', ',');
            }

            $availability = '';
         switch (OPTIONS_AVAILABILITY) {
         case 'quantity': $availability = ($row->quantity > 0 ? 'in stock' : 'out of stock'); break;
         case 'status':   $availability = ($row->prodStatus == 1 ? 'in stock' : 'out of stock'); break;
         case '':         $availability = $row->availability; break;
         default:         $availability = OPTIONS_AVAILABILITY;
      }


            $google_utm = (OPTIONS_ENABLED_GOOGLE_UTM ? OPTIONS_DEFAULT_GOOGLE_UTM : '');

            if (SEO_ENABLED == 'true') {
                $output .= tep_href_link($productURL, $productParam . $row->id) . $google_utm . "\t" .
                    preg_replace($_strip_search, $_strip_replace, strip_tags(strtr($row->name, $_cleaner_array))) . "\t" .
                    preg_replace($_strip_search, $_strip_replace, strip_tags(strtr($row->description, $_cleaner_array))) . "\t" .
                    $row->price . "\t" .
                    $row->image_url . "\t" .
                    $row->id . "\t" .  $availability;
            } else {
                $output .= $row->product_url . $google_utm . "\t" .
                    preg_replace($_strip_search, $_strip_replace, strip_tags(strtr($row->name, $_cleaner_array))) . "\t" .
                    preg_replace($_strip_search, $_strip_replace, strip_tags(strtr($row->description, $_cleaner_array))) . "\t" .
                    $row->price . "\t" .
                    $row->image_url . "\t" .
                    $row->id . "\t" .  $availability;
            }


 if(OPTIONS_ENABLED == 1) {
         if(OPTIONS_ENABLED_AGE_RANGE == 1)
            $output .= "\t" . OPTIONS_AGE_RANGE;
         if(OPTIONS_ENABLED_BRAND == 1)
            $output .= "\t" . (isset($row->brand) ? $row->brand : (strlen(OPTIONS_BRAND) ? OPTIONS_BRAND : "Not Supported"));
         if(OPTIONS_ENABLED_CONDITION == 1)
            $output .= "\t" . OPTIONS_CONDITION;
         if(OPTIONS_ENABLED_CURRENCY == 1)
            $output .= "\t" . OPTIONS_DEFAULT_CURRENCY;
         if(OPTIONS_ENABLED_EXPIRATION == 1)
            $output .= "\t" . $feed_exp_date;
         if(OPTIONS_ENABLED_FEED_LANGUAGE == 1)
            $output .= "\t" . OPTIONS_DEFAULT_FEED_LANGUAGE;
           if(OPTIONS_ENABLED_LABEL == 1)
            $output .= "\t" . OPTIONS_LABEL;
         if(OPTIONS_ENABLED_FEED_QUANTITY == 1)
            $output .= "\t" . $row->quantity;
         if(OPTIONS_ENABLED_GTIN == 1)
            $output .= "\t" . (isset($row->gtin) ? $row->gtin : (strlen(OPTIONS_GTIN) ? OPTIONS_GTIN : "Not Supported"));
         if(OPTIONS_ENABLED_ISBN == 1)
            $output .= "\t" . (isset($row->isbn) ? $row->isbn : (strlen(OPTIONS_ISBN) ? OPTIONS_ISBN : "Not Supported"));
         if(OPTIONS_ENABLED_MADE_IN == 1)
            $output .= "\t" . OPTIONS_MADE_IN;
         if(OPTIONS_ENABLED_MANUFACTURER == 1)
            $output .= "\t" . (in_array($row->mfgName,explode(",",OPTIONS_MANUFACTURERS_NAME_IGNORE)) ? '' : $row->mfgName);
         if(OPTIONS_ENABLED_PAYMENT_ACCEPTED == 1)
            $output .= "\t" . OPTIONS_PAYMENT_ACCEPTED_METHODS;
         if(OPTIONS_ENABLED_PRODUCT_MODEL == 1)
            $output .= "\t" . $row->prodModel;
         if(OPTIONS_ENABLED_PRODUCT_TYPE == 1)
            $output .= "\t" . ((OPTIONS_PRODUCT_TYPE == strtolower('full')) ? $catIndex[$row->prodCatID] : $row->catName);
         if(OPTIONS_ENABLED_SHIPPING == 1)
            $output .= "\t" . OPTIONS_SHIPPING_STRING;
         if(OPTIONS_ENABLED_INCLUDE_TAX == 1)
            $output .= "\t" . OPTIONS_TAX_STRING;
         if(OPTIONS_ENABLED_UPC == 1)
            $output .= "\t" . (isset($row->upc) ? $row->upc : (strlen(OPTIONS_UPC) ? OPTIONS_UPC : "Not Supported"));
         if(OPTIONS_ENABLED_WEIGHT == 1)
            $output .= "\t" . $row->prodWeight . ' ' .OPTIONS_WEIGHT_ACCEPTED_METHODS;

           if (OPTIONS_ENABLED_IDENTIFIER_EXISTS == 1) {
            $icnt = 0;
            if (OPTIONS_ENABLED_BRAND && isset($row->brand)) $icnt++; 
            if (OPTIONS_ENABLED_PRODUCT_MODEL && isset($row->prodModel)) $icnt++;  
            
            if ($icnt > 0) $output .= "\tTRUE";
             else $output .= "\tFALSE";
            
         }

                /******************* BEGIN HANDLING THE ATTRIBUTES ********************/
                if (OPTIONS_ENABLED_ATTRIBUTES == 1) {
                    $products_attributes_query = mysql_query("select count(*) as total from products_options popt, products_attributes patrib where patrib.products_id='" . $row->id . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)1 . "'");
                    $products_attributes = mysql_fetch_object($products_attributes_query);
                    if ($products_attributes->total > 0) {
                        $products_options_name_query = mysql_query("select distinct popt.products_options_id, popt.products_options_name from products_options popt, products_attributes patrib where patrib.products_id='" . (int)$row->id . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)1 . "' order by popt.products_options_name") or die(mysql_error());

                        $trackTabs = '';

                        while ($products_options_name = mysql_fetch_object($products_options_name_query)) {
                            $products_options_array = array();
                            $products_options_query = mysql_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from products_attributes pa, products_options_values pov where pa.products_id = '" . (int)$row->id . "' and pa.options_id = '" . $products_options_name->products_options_id . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)1 . "'");
                            while ($products_options = mysql_fetch_object($products_options_query)) {
                                $products_options_array[] = array('id' => $products_options->products_options_values_id, 'text' => $products_options->products_options_values_name);
                            }

                            for ($a = 0; $a < count($attributesColumns); ++$a) {
                                if ($products_options_name->products_options_name == $attributesColumns[$a]) {
                                    if ($a == 0)
                                        $trackTabs = "\t";
                                    else {
                                        if (empty($trackTabs))
                                            $trackTabs = str_repeat("\t", $a);
                                        $trackTabs .= "\t";
                                    }

                                    $output .= $trackTabs;
                                    foreach ($products_options_array as $arr)
                                        $output .= $arr['text'] . ',';
                                    $output = substr($output, 0, -1);
                                }
                            }
                        }
                    }
                }
                /******************* END HANDLING THE ATTRIBUTES ********************/
            }
            $output .= " \n";
        }






        $already_sent[$row->id] = 1;
        $loop_counter++;

        if ($loop_counter > 750) {
            $fp = fopen($OutFile, "a");
            $fout = fwrite($fp, $output);
            fclose($fp);
            $loop_counter = 0;
            $output = "";
        }
    }

    $fp = fopen($OutFile, "a");
    $fout = fwrite($fp, $output);
    fclose($fp);

    echo '<p style="margin:auto; text-align:left">';
    echo "File completed: <a href=\"" . $OutFile . "\" target=\"_blank\">" . $destination_file . "</a><br> \n";

    
/* $csvFileDest = str_replace('.txt', '.csv', $destination_file);
$csvFileLocn = str_replace('.txt', '.csv', $OutFile);
$csvStr = str_replace("\t", '", "', '"' . $output);
$csvStr = str_replace("\n", " \"\n\" \", $csvStr);
$csvStr = substr($csvStr,0,-1);
$csvStr = str_replace("\t", '", "', '"' . $output . '"');
$fp = fopen( $csvFileLocn , "a" );
$fout = fwrite( $fp , $csvStr );
fclose( $fp );




echo '<p style="margin:auto; text-align:left; padding-top:10px;">';
echo 'Use the following for easier viewing from this page. It is still in development and not meant for anything other than viewing.' . "<br>\n\n";
echo "$completed <a href=\"../" . $csvFileLocn . "\" target=\"_blank\">" . $csvFileDest . "</a><br>\n";
echo '</p>';
*/

chmod($OutFile, 0777);



//Start FTP

    function ftp_file($ftpservername, $ftpusername, $ftppassword, $ftpsourcefile, $ftpdirectory, $ftpdestinationfile)
    {
        // set up basic connection
        $conn_id = ftp_connect($ftpservername);
        if ($conn_id == false) {
            echo "FTP open connection failed to $ftpservername <BR>\n";
            return false;
        }

        // login with username and password
        $login_result = ftp_login($conn_id, $ftpusername, $ftppassword);

        // check connection
        if ((!$conn_id) || (!$login_result)) {
            echo "FTP connection has failed!<BR>\n";
            echo "Attempted to connect to " . $ftpservername . " for user " . $ftpusername . "<BR>\n";
            return false;
        } else {
            echo "Connected to " . $ftpservername . ", for user " . $ftpusername . "<BR>\n";
        }

        if (strlen($ftpdirectory) > 0) {
            if (ftp_chdir($conn_id, $ftpdirectory)) {
                echo "Current directory is now: " . ftp_pwd($conn_id) . "<BR>\n";
            } else {
                echo "Couldn't change directory on $ftpservername<BR>\n";
                return false;
            }
        }

        ftp_pasv($conn_id, true);
        // upload the file
        $upload = ftp_put($conn_id, $ftpdestinationfile, $ftpsourcefile, FTP_ASCII);

        // check upload status
        if (!$upload) {
            echo "$ftpservername: FTP upload has failed!<BR>\n";
            return false;
        } else {
            echo "Uploaded " . $ftpsourcefile . " to " . $ftpservername . " as " . $ftpdestinationfile . "<BR>\n";
        }

        // close the FTP stream
        ftp_close($conn_id);

        return true;
    }

    if (FTP_ENABLED) ftp_file("uploads.google.com", FTP_USERNAME, FTP_PASSWORD, $source_file, "", $destination_file);

//End FTP


//  End TIMER
//  ---------
    $etimer = explode(' ', microtime());
    $etimer = $etimer[1] + $etimer[0];
    echo '<p style="margin:auto; text-align:center">';
    printf("Script timer: <b>%f</b> seconds.", ($etimer - $stimer));
    echo '</p>';
//  ---------

?>
