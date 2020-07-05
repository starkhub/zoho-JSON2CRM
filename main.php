<?php
require 'vendor/autoload.php';

use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

class RecordCreator
{

    public function __construct()
    {
        $configuration = array("client_id" => "HERE YOUR CLIENT ID", "client_secret" => "HERE YOUR CLIENT SECRET", "redirect_uri" => "HERE YOUR REDIRECT URI", "currentUserEmail" => "HERE YOUR CURRENT USER EMAIL", "token_persistence_path" => "vendor/zohocrm/persistence");
        ZCRMRestClient::initialize($configuration);
    }

    public function updateRecords($moduleIns, $existing_id, $u_key, $typeSwitch) // UPDATE AN EXISTING CONTACT OR A LEAD
    {
        if ($typeSwitch == "LEAD") { //Record is a lead
            $inventoryRecords = array();

            $record = ZCRMRecord::getInstance("Leads", $existing_id); // to get the instance of the record

            $record->setFieldValue("Last_Name", $u_key[1]);
            $record->setFieldValue("First_Name", $u_key[2]);
            $record->setFieldValue("Email", $u_key[3]);
            $record->setFieldValue("Phone", $u_key[4]);
            $record->setFieldValue("Creation_Date", $u_key[5]);
            $record->setFieldValue("Role", $u_key[6]);
            $record->setFieldValue("Disabled", $u_key[7]);
            $record->setFieldValue("Status", $u_key[8]);
            $record->setFieldValue("Collaborator", $u_key[9]);
            $record->setFieldValue("Enterprise_Name", $u_key[10]);

            array_push($inventoryRecords, $record); // pushing the record to the array

            $responseIn = $moduleIns->updateRecords($inventoryRecords); // updating the record

            foreach ($responseIn->getEntityResponses() as $responseIns) {//Errors handle
                //echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                //echo "Status:" . $responseIns->getStatus(); // To get response status
                //echo "Message:" . $responseIns->getMessage(); // To get response message
                //echo "Code:" . $responseIns->getCode(); // To get status code
                //echo "Details:" . json_encode($responseIns->getDetails());
                echo "STATUT MAJ DU LEAD = " . $responseIns->getStatus() . "\n";
            }
        } else { //Record is a contact
            $inventoryRecords = array();

            $record = ZCRMRecord::getInstance("Contacts", $existing_id); // to get the instance of the record

            $record->setFieldValue("Last_Name", $u_key[1]);
            $record->setFieldValue("First_Name", $u_key[2]);
            $record->setFieldValue("Email", $u_key[3]);
            $record->setFieldValue("Phone", $u_key[4]);
            $record->setFieldValue("Creation_Date", $u_key[5]);
            $record->setFieldValue("Role", $u_key[6]);
            $record->setFieldValue("Disabled", $u_key[7]);
            $record->setFieldValue("Status", $u_key[8]);
            $record->setFieldValue("Collaborator", $u_key[9]);
            $record->setFieldValue("Enterprise_Name", $u_key[10]);

            array_push($inventoryRecords, $record); // pushing the record to the array

            $responseIn = $moduleIns->updateRecords($inventoryRecords); // updating the record

            foreach ($responseIn->getEntityResponses() as $responseIns) { //Errors handle
                //echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                //echo "Status:" . $responseIns->getStatus(); // To get response status
                //echo "Message:" . $responseIns->getMessage(); // To get response message
                //echo "Code:" . $responseIns->getCode(); // To get status code
                //echo "Details:" . json_encode($responseIns->getDetails());
                echo "STATUT MAJ DU CONTACT = " . $responseIns->getStatus() . "\n";
            }
        }
    }

    public function getRecords($zu_id, $u_key, $moduleIns, $typeSwitch) //FETCH LEAD OR CONTACT DATAS
    { // 2 - Fetch record infos
        $typeSwitch = $typeSwitch;
        $existing_id_json = json_decode($zu_id, true); //2.1 - Decode the zu_id witch is a JSON
        $existing_id = $existing_id_json['id']; //2.2 - Fetch the Zoho record ID
        $moduleIns = $moduleIns; //2.3 - Module instance
        $response = $moduleIns->getRecord($existing_id); //2.4 - Fetch the record
        $record = $response->getData(); //2.5 - Get return
        try {
            //2.6 - FETCH THE VALUES OF ALREADY EXISTING FIELDS
            $zu_id_user = $record->getFieldValue("ID_Utilisateur");
            $zu_lastname = $record->getFieldValue("Last_Name");
            $zu_firstname = $record->getFieldValue("First_Name");
            $zu_email = $record->getFieldValue("Email");
            $zu_phone = $record->getFieldValue("Phone");
            $zu_creation_date = $record->getFieldValue("Creation_Date");
            $zu_role = $record->getFieldValue("Role");
            $zu_disabled = $record->getFieldValue("Disabled");
            $zu_status = $record->getFieldValue("Status");
            $zu_collaborator = $record->getFieldValue("Collaborator");
            $zu_enterprise = $record->getFieldValue("Enterprise_Name");

            $zu_key = array($zu_id_user, $zu_lastname, $zu_firstname, $zu_email, $zu_phone, $zu_creation_date, $zu_role, $zu_disabled, $zu_status, $zu_collaborator, $zu_enterprise); //GENERATE UNIQUE USER KEY FROM ZOHO

            if ($u_key != $zu_key) { //CHECK THE UNIQUE USER KEY WITH ZOHO UNIQUE USER KEY FOR DIFFERENCES
                echo 'LE CONTACT / LEAD EXISTE ET DES INFOS SONT DIFFERENTES !' . "\n";
                self::updateRecords($moduleIns, $existing_id, $u_key, $typeSwitch); //IF THERE'S DIFFERENCES, WE'RE UPDATING THE RECORD !
            } else {
                echo 'RAS - ON CONTINUE...' . "\n";
            }
        } catch (ZCRMException $ex) {
            echo $ex->getMessage(); // To get ZCRMException error message
            echo $ex->getExceptionCode(); // To get ZCRMException error code
            echo $ex->getFile(); // To get the file name that throws the Exception
        }
    }

    public function checkConvert($u_email) //CHECK IF THE RECORD IS A LEAD
    {
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Leads"); // 1.9 - Module instance
        $email = $u_email;
        $param_map = array("page" => 1, "per_page" => 1); // key-value pair containing all the parameters

        try {
            $response = $moduleIns->searchRecordsByEmail($email, $param_map);
            $records = $response->getData();
            foreach ($records as $record) {
                $leadID = $record->getEntityId();
                if ($leadID != "") {
                    return $record->getEntityId();
                }
            }
        } catch (ZCRMException $ex) {
            $message = $ex->getMessage(); // To get ZCRMException error message
            $exeption = $ex->getExceptionCode(); // To get ZCRMException error code
            $file = $ex->getFile(); // To get the file name that throws the Exception
            if ($message == "No Content") {
                return "NOTALEAD";
            }
        }
    }

    public function updateLeadToContact($u_email, $u_key) // UPDATE A CONTACT AFTER LEAD > CONTACT TRANSFORM
    {
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Contacts"); // 1.9 - Module instance
        $email = $u_email;
        $param_map = array("page" => 1, "per_page" => 1); // key-value pair containing all the parameters
        $typeSwitch = "CONTACT";
        try {
            $response = $moduleIns->searchRecordsByEmail($email, $param_map);
            $records = $response->getData();
            foreach ($records as $record) {
                $contactID = $record->getEntityId();
                self::updateRecords($moduleIns, $contactID, $u_key, $typeSwitch);
            }
        } catch (ZCRMException $ex) {
            $message = $ex->getMessage(); // To get ZCRMException error message
            $exeption = $ex->getExceptionCode(); // To get ZCRMException error code
            $file = $ex->getFile(); // To get the file name that throws the Exception
            echo 'OUPS RIEN A FAIRE !';
        }
    }

    public function convertLead($checkConvert, $u_key, $u_email) // LEAD CONVERSION
    {
        $checkConvert = $checkConvert;
 
        $record = ZCRMRestClient::getInstance()->getRecordInstance("Leads", $checkConvert); // To get record instance
        $responseIn = $record->convert();// LEAD CONVERSION !

        echo '3 - LEAD CONVERTI' . "\n";

        self::updateLeadToContact($u_email, $u_key);

    }

    public function createRecords() //MAIN START FUNCTION
    {
        $usersJsonFile = file_get_contents("JSON\mc-users-" . date("dmY") . ".json"); //1 - GET THE USERS JSON FILE
        $jsonArr = json_decode($usersJsonFile, true); //1.1 - Decode JSON to an array
        $Usersarr = $jsonArr['users']; //1.3 - Select the main array inside the JSON, it's where the users are

        echo "INTERFACE MC > ZOHO by Jérémie HOLER" . "\n" . "\n" . "1 - DEMARRAGE : ";

        foreach ($Usersarr as $innerArr) { //1.4 - FOREACH 1 - MAIN ARRAY - For each key => value

            $u_id = $innerArr['id']; //id user
            $u_lastname = $innerArr['lastname']; //nom
            $u_firstname = $innerArr['firstname']; //prenom
            $u_email = $innerArr['email']; //email
            $u_phone = $innerArr['phone']; //telephone
            $u_creation_date = $innerArr['cree_le']; //date de creation
            $u_role = $innerArr['role']['name']; //role
            $u_disabled = $innerArr['desactive']; //disable (desactive ou non)
            $u_status = $innerArr['statut']['name']; //statut(A l'essai, sous contrat, etc.)
            $u_enterpriseArr = $innerArr['entreprises']; //tableau entreprises

            if (!empty($u_enterpriseArr) && is_array($u_enterpriseArr)) { //1.6 - IF 1 - Si entreprises est un tableau (toujours le cas) et qu'il n'est pas vide
                $u_enterprise = $innerArr['entreprises']['name']; //nom entreprise
                $collaboratorArr = $innerArr['entreprises']['collaborateur']; //tableau collaborateur
                if (!empty($collaboratorArr['prenom']) && !empty($collaboratorArr['nom'])) { //1.7 - IF 2 - Si collaborateur est un tableau (toujours le cas) et qu'il n'est pas vide
                    $u_collaborator = $collaboratorArr['prenom'] . ' ' . $collaboratorArr['nom']; //nom et prénom du collaborateur
                } else { //IF 2 - Sinon collaborateur = N/A
                    $u_collaborator = "N/A";
                }
            } else { //IF 1 - Sinon collaborateur et entreprise = N/A
                $u_enterprise = "N/A";
                $u_collaborator = "N/A";
            }

            $u_key = array($u_id, $u_lastname, $u_firstname, $u_email, $u_phone, $u_creation_date, $u_role, $u_disabled, $u_status, $u_collaborator, $u_enterprise); //1.8 - //GENERATE UNIQUE USER KEY FROM JSON

            if ($u_status == "A l'essai" || $u_status == "Essai fini" || $u_status == "Refusé" || $u_status == "En négo" || $u_status == "Résilié") { // NEW - SI L'USER EST EN ESSAI ON INVOQUE LE MODULE LEADS

                $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Leads"); // 1.9 - Module instance

                $typeSwitch = "LEAD";

                $records = array(); // 1.9.1 - RECORDS ARRAY

                $record = ZCRMRecord::getInstance("Leads", null); // Instance du module Contacts
                $record->setFieldValue("ID_Utilisateur", $u_id); // 1.9.2 - On déclare les valeurs d'après les variables récupérés ci-dessus
                $record->setFieldValue("Last_Name", $u_lastname);
                $record->setFieldValue("First_Name", $u_firstname);
                $record->setFieldValue("Email", $u_email);
                $record->setFieldValue("Phone", $u_phone);
                $record->setFieldValue("Creation_Date", $u_creation_date);
                $record->setFieldValue("Role", $u_role);
                $record->setFieldValue("Disabled", $u_disabled);
                $record->setFieldValue("Status", $u_status);
                $record->setFieldValue("Collaborator", $u_collaborator);
                $record->setFieldValue("Enterprise_Name", $u_enterprise);
                $record->setFieldValue("Lead_Source", "API_SYNC");

                array_push($records, $record); // 1.9.3 - PUSH VALUES INSIDE THE ARRAY RECORDS

                $responseIn = $moduleIns->createRecords($records); // CREATE RECORD INSIDE ZOHO

                foreach ($responseIn->getEntityResponses() as $responseIns) { // 1.9.4 - Errors Handle
                    //echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                    //echo "Status:" . $responseIns->getStatus(); // To get response status
                    //echo "Message:" . $responseIns->getMessage(); // To get response message
                    //echo "Code:" . $responseIns->getCode(); // To get status code
                    //echo "Details:" . json_encode($responseIns->getDetails());

                    echo "2 - On traite un LEAD " . "\n";
                    echo "USER_ID = " . $u_id . "\n";
                    echo "Email = " . $u_email . "\n";

                    $zu_id = json_encode($responseIns->getDetails()); // ID record Zoho

                    if ($responseIns->getCode() == "DUPLICATE_DATA") { //1.9.5 - Si le code de retour est "DUPLICATE_DATE"

                        echo 'LE LEAD EXISTE DEJA - RECUPERATION DES INFOS ' . "\n";

                        self::getRecords($zu_id, $u_key, $moduleIns, $typeSwitch); //1.9.6 - On envoie l'ID du record, la clé unique de l'user et l'instance moduleIns vers la fonction getRecords

                    } else {

                        echo '3 - NOUVEAU LEAD ENREGISTRÉ' . "\n";
                    }
                }
            } else { // NEW - SINON ON INVOQUE LE MODULE CONTACTS

                $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance("Contacts"); // 1.9 - Instance du module
                $typeSwitch = "CONTACT";

                $records = array(); // 1.9.1 - Ouverture du tableau records

                $record = ZCRMRecord::getInstance("Contacts", null); // Instance du module Contacts
                $record->setFieldValue("ID_Utilisateur", $u_id); // 1.9.2 - On déclare les valeurs d'après les variables récupérés ci-dessus
                $record->setFieldValue("Last_Name", $u_lastname);
                $record->setFieldValue("First_Name", $u_firstname);
                $record->setFieldValue("Email", $u_email);
                $record->setFieldValue("Phone", $u_phone);
                $record->setFieldValue("Creation_Date", $u_creation_date);
                $record->setFieldValue("Role", $u_role);
                $record->setFieldValue("Disabled", $u_disabled);
                $record->setFieldValue("Status", $u_status);
                $record->setFieldValue("Collaborator", $u_collaborator);
                $record->setFieldValue("Enterprise_Name", $u_enterprise);
                $record->setFieldValue("Lead_Source", "API_SYNC");

                array_push($records, $record); // 1.9.3 - On pousse les couples clé / valeur dans le tableau records

                echo "2 - ON TRAITE UN CONTACT - CHECK SI C'EST UNE CONVERSION " . "\n";
                echo "USER_ID = " . $u_id . "\n";
                echo "Email = " . $u_email . "\n";

                $checkConvert = self::checkConvert($u_email);// On vérifie si le contact est un LEAD

                if ($checkConvert != "NOTALEAD") { // SI LE MESSAGE DE RETOUR DE LA FONCTION DE VERIFICATION EST UN ID ZOHO

                    echo "C'EST UN ANCIEN LEAD - ON CONVERTIT !" . "\n";
                    self::convertLead($checkConvert, $u_key, $u_email); // ON CONVERTIT LE LEAD AVEC SON ID ZOHO + UN UPDATE

                } else {

                    $responseIn = $moduleIns->createRecords($records); // on créé le record dans Zoho

                    foreach ($responseIn->getEntityResponses() as $responseIns) { // 1.9.4 - Pour chaque réponse on récupère les différents codes
                        //echo "HTTP Status Code:" . $responseIn->getHttpStatusCode(); // To get http response code
                        //echo "Status:" . $responseIns->getStatus(); // To get response status
                        //echo "Message:" . $responseIns->getMessage(); // To get response message
                        //echo "Code:" . $responseIns->getCode(); // To get status code
                        //echo "Details:" . json_encode($responseIns->getDetails());

                        echo "CE N'EST PAS UN LEAD - CREATION D'UN CONTACT" . "\n";

                        $zu_id = json_encode($responseIns->getDetails()); // ID record Zoho

                        if ($responseIns->getCode() == "DUPLICATE_DATA") { //1.9.5 - Si le code de retour est "DUPLICATE_DATE"

                            echo 'LE CONTACT EXISTE DEJA - RECUPERATION DES INFOS ' . "\n";

                            self::getRecords($zu_id, $u_key, $moduleIns, $typeSwitch); //1.9.6 - On envoie l'ID du record, la clé unique de l'user et l'instance moduleIns vers la fonction getRecords

                        } else {
                            echo '3 - NOUVEAU CONTACT ENREGISTRÉ !' . "\n";
                        }
                    }
                }
            } // NEW - FIN DU ELSE 

            sleep(1);
        } //Foreach1

    }
}

$obj = new RecordCreator(); // NOUVELLE INSTANCE DU MODULE RecordCreator
$obj->createRecords(); // RDV A LA FONCTION DU MÊME NOM POUR LE DÉBUT DES INSTRUCTION ! :=)
