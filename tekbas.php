<?php
ini_set('max_execution_time', 0);
$json_customers =  file_get_contents("customers.json");
$json_customers_data = NULL;
$json_customers_data = json_decode($json_customers,true);

$json_animals =  file_get_contents("animals.json");
$json_animals_data = NULL;
$json_animals_data = json_decode($json_animals,true);

$db = new SQLite3('sahiplenme.db');


//CLASS YAPISI VE ADAPTER DESIGN-----------------------------------------------------------------------

class Customers {
    private $first_name;
    private $last_name;
	private $email;
    private $gender;
    function __construct($first_in, $last_in, $email_in, $gender_in) {
        $this->first_name = $first_in;
        $this->last_name  = $last_in;
		$this->email = $email_in;
        $this->gender  = $gender_in;
    }
    function getFirst() {
        return $this->first_name;
    }
    function getLast() {
        return $this->last_name;
    }
	function getEmail() {
        return $this->email;
    }
	function getGender() {
        return $this->gender;
    }
}



class AnimalAdapter {
    private $customer_info;
	private $animal;
	private $animal_id;
	private $db;
	
    function __construct(Customers $customer_info_in,SQLite3 $db_in) {
        $this->customer_info = $customer_info_in;
		$this->db = $db_in;
	   }
	   
	function addAnimal($animal_in,$animal_id_in){
		$this->animal = $animal_in;
		$this->animal_id = $animal_id_in;
		
	}
	
	function getAnimal(){
		return $this->animal;
	}
	
	function getAnimalId(){
		return $this->animal_id;
	}
	
    
function getOwnership(){
		$stringo = $this->customer_info->getEmail() . "";
		$stringo2 = $this->getAnimalId();		
		$this->db->exec("UPDATE animals SET email = '$stringo' WHERE id = '$stringo2'");
		$this->db->exec("UPDATE animals SET is_owned = 1 WHERE id= '$stringo2'");
		$this->db->exec("UPDATE customers SET count = count - 1 WHERE email = '$stringo' "); 
        //return $this->customer_info->getFirst()." ".$this->customer_info->getLast() ." owned ". $this->getAnimal(). "<br>";
    
}

}


// VERİTABANINA VERİ EKLEME----------------------------------------------------------------------------------------------------------------------------------------


if($json_customers_data != NULL){
foreach ($json_customers_data as $key1 => $value1) {
	if($json_customers_data[$key1]["gender"] == "Male"){
		$customer = $json_customers_data[$key1];
		$customer["first_name"] = str_replace("'", ' ', $customer["first_name"]);
		$customer["last_name"] = str_replace("'", ' ', $customer["last_name"]);
		$sql ="INSERT OR IGNORE INTO customers VALUES ('$customer[id]','$customer[first_name]','$customer[last_name]','$customer[email]','$customer[gender]',1)";
		$result = $db->exec($sql); }
	
	else if($json_customers_data[$key1]["gender"] == "Female"){
		$customer = $json_customers_data[$key1];
		$customer["first_name"] = str_replace("'", ' ', $customer["first_name"]);
		$customer["last_name"] = str_replace("'", ' ', $customer["last_name"]);
		$sql ="INSERT OR IGNORE INTO customers VALUES ('$customer[id]','$customer[first_name]','$customer[last_name]','$customer[email]','$customer[gender]',3)";
		$result = $db->exec($sql); }
	
    }
}


if($json_animals_data != NULL){
foreach ($json_animals_data as $key1 => $value1) {
		$animal = $json_animals_data[$key1];
		$animal["animal_name"] = str_replace("'", ' ', $animal["animal_name"]);
		$sql = "INSERT INTO animals VALUES ('$animal[id]','$animal[animal_name]',NULL,0)";
		$result = $db->exec($sql); 
	}
}



//HAYVANLARI SAHİPLENDİRME------------------------------------------------------------------------------------------------------------------------------------------



$i = 0;
$k = 0;
foreach($json_customers_data as $key1=> $value1){
	
	
	$customer = $json_customers_data[$key1];
	$owner = new Customers("$customer[first_name]","$customer[last_name]","$customer[email]","$customer[gender]");
	$owneradapter = new AnimalAdapter($owner,$db);
	
	if($customer["gender"]== "Male"){
		
		$i++;
	for($k;$k<$i;$k++){
		$animal = $json_animals_data[$k];
		$owneradapter->addAnimal("$animal[animal_name]","$animal[id]");
		$data2 = $owneradapter->getOwnership();
		print_r($data2);
		if($json_animals_data[$k]==NULL)
		break;
				
	}
	
	}
	else if($customer["gender"]== "Female"){
		
		$i+=3; 
		for($k;$k<$i;$k++){
		$animal = $json_animals_data[$k];
		$owneradapter->addAnimal("$animal[animal_name]","$animal[id]");
		$data2 = $owneradapter->getOwnership();
		print_r($data2);
		
		}	
	}
	if(empty($json_animals_data[$k]))
		break;
}





//SAHİPLERİN VE HAYVANLARIN KİMLER OLDUĞUNU GÖRÜNTÜLEME-----------------------------------------------------------------------------------------------------------------


$sql_customer = "SELECT first_name,last_name,count FROM customers WHERE count != 0";
$sql_animal = "SELECT animal_name FROM animals WHERE is_owned = 0";
$sql_mail_customer = "SELECT email,first_name,last_name FROM customers";
$sql_mail_animal = "SELECT email,animal_name FROM animals";

$return = $db->query($sql_mail_animal);
$return2 = $db->query($sql_mail_customer);

$return3 = $db->query($sql_customer);
$return4 = $db->query($sql_animal);

echo "--------------OWNED ANIMALS AND OWNERS------------------" . "<br>";

while($res = $return->fetchArray(SQLITE3_ASSOC)){
	
		while($res2 = $return2->fetchArray(SQLITE3_ASSOC)){
			if($res['email']==$res2['email'])
				echo $res2['first_name'] . " " . $res2['last_name'] . " take " . $res['animal_name'] . "<br>";
			
		}
	}

echo "<br><br><br>" . "--------------NO OWNER CUSTOMERS-------------------" . "<br>";

while($res3 = $return3->fetchArray(SQLITE3_ASSOC)){
	echo $res3['first_name'] . " " . $res3['last_name'] . " could take " . $res3['count'] . " more animal. " ."<br>";
}	


echo "<br><br><br>" . "--------------NOT OWNED ANIMALS--------------------" . "<br>";

while($res4 = $return4->fetchArray(SQLITE3_ASSOC)){
	echo $res4['animal_name'] . " is not owned!" ."<br>";
}		
?>


