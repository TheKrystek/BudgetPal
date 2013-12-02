<?php

class mainClass
{

    var $mysqli;
    var $DBHOST;
    var $DBNAME;
    var $DBUSER;
    var $DBPASS;
    var $userId;
	var $DEBUG;
    public function __construct($user, $password, $host, $database) 
    {
        $this->DBUSER = $user;
        $this->DBPASS = $password;
        $this->DBHOST = $host;
        $this->DBNAME = $database;
        $this->InitializeSession();
        $this->DEBUG = true;
        if (isset($_SESSION['userId']))
        $this->userId = $_SESSION['userId'];
        else
            $this->userId = 0;
    }
    
    private function Debug($s){
    	if ($this->DEBUG){
    	    echo '<h3>DEBUG</h3>';
    	    echo '<div style="border:2px solid blue;padding:10px;">';
    	    echo 'ERROR: '.$s->error."<br/>";
    	    echo 'AFFECTED ROWS: '.$s->affected_rows."<br/>";    
    	    printf("ARGUMENTS: %d <br>",$s->param_count);
    	    echo '</div>';
    	}
    } 
    
    public function Connect()
    {
    	$this->mysqli = new mysqli($this->DBHOST, $this->DBUSER, $this->DBPASS, $this->DBNAME);
    	$this->mysqli->connect_error;
    }
    
    public function Close()
    {
    	$this->mysqli->close();
    }
    
    public function UserPrint()
    {
    	$result = $this->mysqli->query("SELECT * FROM Uzytkownicy");
    	while ($row = $result->fetch_assoc())
    		echo "<tr><td>{$row['ID_Uzytkownika']}.</td><td>{$row['login']}</td><td>{$row['email']}</td><td>{$row['dataRejestracji']}</td><td>".substr($row['haslo'],0,30)."...</td></tr>";
    }

    /**
     * @desc Sprawdza czy uzytkownik jest zalogowany
     * @param void
     * @return bool
     */
    public function isLogged()
    {
    	if(isset($_SESSION['userId'], $_SESSION['username'], $_SESSION['login_String'])) {	
    		$login_String = $_SESSION['login_String'];
    		$username = $_SESSION['username'];
    		$user_browser = $_SERVER['HTTP_USER_AGENT'];
    		if ($s = $this->mysqli->prepare("SELECT haslo FROM Uzytkownicy WHERE ID_Uzytkownika = ? LIMIT 1")) {
    			$s->bind_param('i', $this->userId);
    			$s->execute();
    			$s->store_result();
    			if($s->num_rows == 1) {
    				$s->bind_result($password);
    				$s->fetch();
    				$login_check = hash('sha512', $password.$user_browser);
    				if($login_check == $login_String) {
    					return true;
    				}
    			}
    		}
    	}
    	return false;
    }	
    
    
    
    private function InitializeSession() 
    {
        $session_name = 'myBudgetPal';
        ini_set('session.use_only_cookies', 0);
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"]);
        session_name($session_name);
        session_start();
        session_regenerate_id();
    }


    private function isConnected() 
    {
        if (!$this->mysqli->connect_error)
            return true;
        else
            return false;
    }
    
    private function UserNameAlreadyExists($username)
    {
    	if ($s = $this->mysqli->prepare("SELECT login FROM Uzytkownicy where login = ?")) {
    		$s->bind_param('s', $username);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows == 1)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    
    
    private function UserEmailAlreadyExists($email)
    {
    	if ($s = $this->mysqli->prepare("SELECT email FROM Uzytkownicy where email = ?")) {
    		$s->bind_param('s', $email);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows == 1)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    
    
    
    private function GetBudgetByName($name)
    {
    	
    	if ($s = $this->mysqli->prepare("SELECT ID_Budzetu FROM Budzet where ID_Uzytkownika = ? AND nazwa= ?")) {
    		$s->bind_param('is',$this->userId,$name);
    		$s->execute();
    		$s->bind_result($ID_Budzetu);
    		$s->store_result();
    		$s->fetch();
    		if ($s->num_rows > 0)
    			return $ID_Budzetu;
    		else
    			return false;
    	}
    	return false;
    }
    
    private function DoesBudgetExist($budgetId)
    {
    	
    	if ($s = $this->mysqli->prepare("SELECT ID_Budzetu FROM Budzet where ID_Uzytkownika = ? AND ID_Budzetu = ?")) {
    		$s->bind_param('ii',$this->userId,$budgetId);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows > 0)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    

    
    
    private function GetProductCategoryByName($name)
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_KatProduktu FROM KategorieProduktow where nazwa = ?")) {
    		$s->bind_param('s', $name);
    		$s->execute();
    		$s->bind_result($ID_KatProduktu);
    		$s->store_result();
    		$s->fetch();
    		if ($s->num_rows > 0)
    			return $ID_KatProduktu;
    		else
    			return false;  		
    	}
    	return false;
    
    }
    
    private function DoesProductCategoryExist($product_cat)
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_KatProduktu FROM KategorieProduktow where ID_KatProduktu = ?")) {
    		$s->bind_param('i', $product_cat);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows == 1)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    
    
    //Zwraca id kategorii przychodu
    private function GetIncomeCategoryByName($name)
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_KatPrzychodu FROM KategoriePrzychodow where nazwa = ?")) {
    		$s->bind_param('s', $name);
    		$s->execute();
    		$s->bind_result($ID_KatPrzychodu);
    		$s->store_result();
    		$s->fetch();
    		if ($s->num_rows > 0)
    		    return $ID_KatPrzychodu;
    		else
    			return false;
    	}
    	return false;
    
    }
    
    private function DoesIncomeCategoryExist($incomeCat)
    {
    	if ($s = $this->mysqli->prepare("SELECT nazwa FROM KategoriePrzychodow where ID_KatPrzychodu = ?")) {
    		$s->bind_param('i', $incomeCat);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows == 1)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    
    

    private function GetProductByName($name)
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_Produktu FROM Produkty where nazwa = ?")) {
    		$s->bind_param('s', $name);
    		$s->execute();
    		$s->bind_result($ProductID);
    		$s->store_result();
    		$s->fetch();
    		if ($s->num_rows > 0)
    			return $ProductID;
    		else
    			return false;
    	}
    	return false;
    }
    

    
    
    private function DoesProductExist($productId)
    {
    	if ($s = $this->mysqli->prepare("SELECT nazwa FROM Produkty where ID_Produktu = ?")) {
    		$s->bind_param('i', $productId);
    		$s->execute();
    		$s->store_result();
    		if ($s->num_rows == 1)
    			return true;
    		else
    			return false;
    	}
    	return false;
    }
    
    
    
    private function OrderASCBy($sql, $columns){
    	return $sql . " ORDER BY ".$columns." ASC";
    }
    
    private function OrderDESCBy($sql, $columns){
    	return $sql . " ORDER BY ".$columns." DESC";
    }
    
    private function Limit($sql, $limit){
    	return $sql . " LIMIT ".$limit;
    }
    
	private function OrderBy($sql, $columns, $mode)
	{
		if ($mode == 'ASC')
			return $this->OrderASCBy($sql, $columns);
		else if ($mode == 'DESC')
			return $this->OrderDESCBy($sql, $columns);
		else
			return status('NO_SUCH_ORDER');
	}
	
	private function GetRecentScheduledIncome($budgetId, $name, $categoryId, $amount, $date)
	{
	    if ($s = $this->mysqli->prepare("SELECT ID_PlanowanegoDochodu FROM PlanowanyDochod where ID_Budzetu = ? AND nazwa = ? AND ID_KatPrzychodu = ? AND kwota = ? AND data = ?")) {
	        $s->bind_param('isids', $budgetId, $name, $categoryId, $amount, $date);
	        $s->execute();
	        $s->bind_result($ID_PlanowanegoDochodu);
	        $s->store_result();
	        $s->fetch();
	        if ($s->num_rows > 0)
	            return $ID_PlanowanegoDochodu;
	        else
	            return false;
	    }
	    return false;
	}
   
	private function GetRecentScheduledExpense($budgetId, $productId, $amount, $date)
	{
	    if ($s = $this->mysqli->prepare("SELECT ID_PlanowanegoWydatku FROM PlanowanyWydatek where ID_Budzetu = ? AND ID_Produktu = ? AND kwota = ? AND data = ?")) {
	        $s->bind_param('iids', $budgetId, $productId, $amount, $date);
	        $s->execute();
	        $s->bind_result($ID_PlanowanegoWydatku);
	        $s->store_result();
	        $s->fetch();
	        if ($s->num_rows > 0)
	            return $ID_PlanowanegoWydatku;
	        else
	            return false;
	    }
	    return false;
	}
	

	private function DoesNotificationExist($notificationId)
	{
	    if ($s = $this->mysqli->prepare("SELECT ID_Powiadomienia FROM Powiadomienia where ID_Powiadomienia = ?")) {
	        $s->bind_param('i', $notificationId);
	        $s->execute();
	        $s->store_result();
	        if ($s->num_rows == 1)
	            return true;
	        else
	            return false;
	    }
	    return false;
	}
	


    /** 
     * @desc Funkcja rejestruje uzytkownika
     * @param String, String, String
     * @return boolean
     * @example krystek, trunde, krystek@example.com
     * @logged false
     */
    public function Register($login, $password, $email) 
    {
        if (!$this->UserNameAlreadyExists($login)) {
            if (!$this->UserEmailAlreadyExists($email)) {
                if ($s = $this->mysqli->prepare("INSERT INTO Uzytkownicy (login, haslo, email, ip, userAgent, potwierdzony) values (?, ?, ?, ?, ?, 0)")) {
                    $password = hash('sha256',$password);
                    $s->bind_param('sssss', $login,$password,$email,$_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT']);
                    $s->execute();
                    $s->store_result();
                }
                return status('REGISTERED');
            }
            else
                return status('EMAIL_TAKEN');
        }
        else
            return status('USERNAME_TAKEN');
    }

	/** 
	  * @desc Zaloguj uzytkownika (haslo <b>password</b>)
	  * @param String, String
	  * @return boolean
	  * @example test, 5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8
	  * @logged false
	  */
    public function Login($user, $password)
    {
        if ($this->UserNameAlreadyExists($user)) {
            if ($s = $this->mysqli->prepare("SELECT ID_Uzytkownika, login, haslo FROM Uzytkownicy where login = ? AND haslo = ?")) {
                $s->bind_param('ss', $user,$password);
                $s->execute();
                $s->store_result();
                $s->bind_result($this->userId, $username, $password);
                $s->fetch();
                if ($s->num_rows == 1) {
                    // Poprawnie zalogowano
                    $user_browser = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['userId'] = $this->userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['login_String'] = hash('sha512', $password.$user_browser);
                    return status('LOGGED_IN');
                }
            }
        }
        return status('WRONG_PASS');
    }




    /** 
      * @desc Wylogowuje uztykownika
      * @param void
      * @return boolean
      * @example void
      * @logged true
      */
    public function Logout() 
    {
        $_SESSION = array();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"]);
        session_destroy();
        return status('LOGGED_OUT');
    }

    /** 
      * @desc Zwraca liste budzetow nalezacych do uzytkownika
      * @param void
      * @return Budgets
      * @example void
      * @logged true
      */
    public function GetBudgets() 
    {
            if ($s = $this->mysqli->prepare("SELECT ID_Budzetu, nazwa, opis FROM Budzet where ID_Uzytkownika = ?")) {
                $s->bind_param('i', $this->userId);
                $s->execute();
                $s->bind_result($ID_Budzetu,$nazwa,$opis);
                $arr = array();
                while ( $s->fetch() ) {
                    $row = array('ID_Budzetu' => $ID_Budzetu,'nazwa' => $nazwa,'opis' => $opis);
                    $arr[] = $row;
                }
                return array('count' =>  $s->num_rows,
                             'budgets' => $arr);
            }
            else
                return status('NO_BUDGETS');
    }
   
    
    /** 
      * @desc Dodaje budzet 
      * @param String, String
      * @return boolean
      * @example testowy, Testowy opis budzetu
      * @logged true
      */
    public function AddBudget($name, $description)
    {
        if ($this->GetBudgetByName($name))
        	return status('BUDGET_EXISTS');
        else{
	    	if ($s = $this->mysqli->prepare("INSERT INTO Budzet (ID_Uzytkownika,nazwa,opis) values (?, ?, ?);")) {
	    		$s->bind_param('iss',$this->userId,$name, $description);
	    		$s->execute();
	    		$s->bind_result();
	    		return status('BUDGET_ADDED');
	    	}
	    	else
	    		return status('BUDGET_NOT_ADDED');
	    }
    }
    
    /**
     * @desc Modyfikuje zdefiniowany budzet
     * @param int, String, String
     * @return boolean
     * @example 14, Nowa nazwa, zmieniony opis
     * @logged true
     */
    public function UpdateBudget($budgetId,$name,$description)
    {
    	if (!$this->DoesBudgetExist($budgetId))
    		return status('NO_SUCH_BUDGET');
    	{
    		// Pobierz stare wartości
    		if ($s = $this->mysqli->prepare("SELECT nazwa, opis FROM Budzet where where ID_Uzytkownika = ? ID_Budzetu = ?")) {
    			$s->bind_param('ii',$this->userId, $budgetId);
    			$s->execute();
    			$s->bind_result($nazwa,$opis);
    			$arr = array();
    			$s->fetch();
    			if (empty($name))
    				$name = $nazwa;
    			if (empty($description))
    				$description = $opis;
    		}
    		if ($s = $this->mysqli->prepare("UPDATE Budzet set nazwa = ?, opis = ? where ID_Uzytkownika = ? AND ID_Budzetu = ?;")) {
    			$s->bind_param('ssii',$name,$description,$this->userId,$budgetId);
    			$s->execute();
    			$s->bind_result();
    			return status('BUDGET_UPDATED');
    		}
    		else
    			return status('BUDGET_NOT_UPDATED');
    	}
    }
    
    /** 
      * @desc Usuwa zdefiniowany budzet
      * @param int
      * @return boolean
      * @example 14
      * @logged true
      */
    public function DeleteBudget($budgetId)
    {
        if (!$this->DoesBudgetExist($budgetId))
        		return status('NO_SUCH_BUDGET');
        { 
	    	if ($s = $this->mysqli->prepare("DELETE FROM Budzet where ID_Uzytkownika = ? AND ID_Budzetu = ?;")) {
	    		$s->bind_param('ii',$this->userId,$budgetId);
	    		$s->execute();
	    		$s->bind_result();
	    		return status('BUDGET_DELETED');
	    	}
	    	else
	    		return status('BUDGET_NOT_DELETED');
        }
    }
    
    
    /**
     * @desc Pobiera listę kategorii produktów
     * @param void
     * @return ProductsCategories
     * @example void
     * @logged true
     */
    public function GetProductCategories()
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_KatProduktu, nazwa FROM KategorieProduktow")) {
    		$s->execute();
    		$s->bind_result($ID_KatProduktu,$nazwa);
    		$arr = array();
            	while ( $s->fetch() ) {
               		$row = array('ID_KatProduktu' => $ID_KatProduktu,'nazwa' => $nazwa);
                    $arr[] = $row;
                }
                return array('count' =>  $s->num_rows,
                             'categories' => $arr);
    	}
    	else
    		return status('CANNOT_GET_PRODUCT_CATEGORIES');
    }


    /** 
      * @desc Dodaje kategorie do listy kategorii produktow
      * @param String
      * @return boolean
      * @example owoce
      * @logged true
      */
    public function AddProductCategory($name)
    {
    	if ($this->GetProductCategoryByName($name))
    		return status('PRODUCT_CATEGORY_EXISTS');
    	else{
    		if ($s = $this->mysqli->prepare("INSERT INTO KategorieProduktow (nazwa) values (?);")) {
    			$s->bind_param('s',$name);
    			$s->execute();
    			$s->bind_result();
    			return status('PRODUCT_CATEGORY_ADDED');
    		}
    		else
    			return status('PRODUCT_CATEGORY_NOT_ADDED');
    	}
    }

    /**
     * @desc Pobiera listę kategorii przychodow
     * @param void
     * @return IncomeCategories
     * @example void
     * @logged true
     */
    public function GetIncomeCategories()
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_KatPrzychodu, nazwa FROM KategoriePrzychodow")) {
    		$s->execute();
    		$s->bind_result($ID_KatPrzychodu,$nazwa);
    		$arr = array();
    		while ( $s->fetch() ) {
    			$row = array('ID_KatPrzychodu' => $ID_KatPrzychodu,'nazwa' => $nazwa);
    			$arr[] = $row;
    		}
    		return array('count' =>  $s->num_rows,
    				'categories' => $arr);
    	}
    	else
    		return status('CANNOT_GET_INCOMES_CATEGORIES');
    }
    
    
    /**
     * @desc Dodaje kategorie do listy kategorii przychodow
     * @param String
     * @return boolean
     * @example pensja
     * @logged true
     */
    public function AddIncomeCategory($name)
    {
    	if ($this->GetIncomeCategoryByName($name))
    		return status('INCOME_CATEGORY_EXISTS');
    	else{
    		if ($s = $this->mysqli->prepare("INSERT INTO KategoriePrzychodow (nazwa) values (?);")) {
    			$s->bind_param('s',$name);
    			$s->execute();
    			$s->bind_result();
    			return status('INCOME_CATEGORY_ADDED');
    		}
    		else
    			return status('INCOME_CATEGORY_NOT_ADDED');
    	}
    }
    
    
    
    
    /**
     * @desc Dodaje produkt do listy produktow
     * @param String, int
     * @return boolean
     * @example jablko, 1
     * @logged true
     */
    public function AddProduct($name, $product_cat = 1)
    {
    	if (!$this->DoesProductCategoryExist($product_cat))
    		return status('PRODUCT_CATEGORY_NOT_EXISTS');
    	
    	if ($this->GetProductByName($name))
    		return status('PRODUCT_EXISTS');
    	else{
    		if ($s = $this->mysqli->prepare("INSERT INTO Produkty (ID_KatProduktu, nazwa) values (?, ?);")) {
    			$s->bind_param('is',$product_cat,$name);
    			$s->execute();
    			$s->bind_result();
    			return status('PRODUCT_ADDED');
    		}
    		else
    			return status('PRODUCT_NOT_ADDED');
    	}
    }  
    
    
    /**
     * @desc Pobiera listę produktów
     * @param void
     * @return Products
     * @example void
     * @logged true
     */
    public function GetProducts()
    {
    	if ($s = $this->mysqli->prepare("SELECT ID_Produktu, ID_KatProduktu, nazwa, data FROM Produkty")) {
    		$s->execute();
    		$s->bind_result($ID_Produktu,$ID_KatProduktu,$nazwa,$data);
    		$arr = array();
    		while ( $s->fetch() ) {
    			$row = array('ID_Produktu' => $ID_Produktu,'ID_KatProduktu' => $ID_KatProduktu,'nazwa' => $nazwa, 'data' => $data);
    			$arr[] = $row;
    		}
    		return array('count' =>  $s->num_rows,
    				'products' => $arr);
    	}
    	else
    		return status('CANNOT_GET_PRODUCTS');
    }
    
    /**
     * @desc Pobiera listę wydatków ze wskazanego budzetu
     * @param int
     * @return Expenses
     * @example 1
     * @logged true
     */
    public function GetExpenses($budgetId)
    {
    	if ($this->DoesBudgetExist($budgetId)){
	    	if ($s = $this->mysqli->prepare("SELECT W.ID_Wydatku,W.ID_Produktu, P.nazwa, W.kwota, W.data 
	    			FROM Wydatki W join Produkty P on W.ID_Produktu = P.ID_Produktu where W.ID_Budzetu = ?")) {
	    		$s->bind_param('i', $budgetId);
	    		$s->execute();
	    		$s->bind_result($ID_Wydatku,$ID_Produktu, $nazwa, $kwota, $data);
	    		$arr = array();
	            	while ( $s->fetch() ) {
	               		$row = array('ID_Wydatku' => $ID_Wydatku,'ID_Produktu' => $ID_Produktu,'nazwa' => $nazwa,'kwota' => $kwota, 'data' => $data);
	                    $arr[] = $row;
	                }
	                return array('count' =>  $s->num_rows,
	                             'expenses' => $arr);
	    	}
	    	else
	    		return status('CANNOT_GET_EXPENSES');
    	}
    	else
    		return status('NO_SUCH_BUDGET');
    }
    
    
    /**
     * @desc Dodaje nowy wydatek
     * @param int, String, double, int
     * @return boolean
     * @example 3, jablko, 1.3, 1
     * @logged true
     */
    public function AddExpense($budgetId,$name,$amount,$purchaseId = -1)
    {
    	if (empty($purchaseId))
    		$purchaseId = null;
    	
    	// Jezeli dany produkt nie istnieje to dodajemy go do listy z kategoria produktow 'inny'
    	if (!$this->GetProductByName($name))
 	  		$this->AddProduct($name, 1);
		
    	$productId = $this->GetProductByName($name);              
        if (!$this->DoesBudgetExist($budgetId))
        	return status('NO_SUCH_BUDGET');
        else{
	    	if ($s = $this->mysqli->prepare("INSERT INTO Wydatki (ID_Budzetu,ID_Produktu,kwota,ID_Zakupu) values (?, ?, ?, ?);")) {
	    		$s->bind_param('iidi',$budgetId,$productId,$amount,$purchaseId);
	    		$s->execute();
	    		$s->bind_result();
	    		return status('EXPENSE_ADDED');
	    	}
	    	else
	    		return status('EXPENSE_NOT_ADDED');
	    }
    }
    
    ///TODO implementacja tej metody
    /**
     * @desc Edytuje wydatek
     * @param int, String, double, int
     * @return boolean
     * @example 3, jablko, 1.3, 1
     * @logged true
     */
    public function UpdateExpense($expenseId,$name,$amount,$purchaseId = -1)
    {
    /*
    	if (empty($purchaseId))
    		$purchaseId = null;
    	 
    	if (!$this->GetProductByName($name))
    		$this->AddProduct(1, $name);
    
    	$productId = $this->GetProductByName($name);
    	
    
    	if (!$this->DoesBudgetExist($this->userId,$budgetId))
    		return status('NO_SUCH_BUDGET');
    	else{
    		if ($s = $this->mysqli->prepare("INSERT INTO Wydatki (ID_Budzetu,ID_Produktu,kwota) values (?, ?, ?);")) {
    			$s->bind_param('iid',$budgetId,$productId,$amount);
    			$s->execute();
    			$s->bind_result();
    			return status('EXPENSE_ADDED');
    		}
    		else
    			return status('EXPENSE_NOT_ADDED');
    	}
    	*/
    	return status('STUB_METHOD');
    }
    
    ///TODO implementacja tej metody
    /**
     * @desc Usuwa zdefiniowany wydatek
     * @param int
     * @return boolean
     * @example 12
     * @logged true
     */
    public function DeleteExpense($expenseId)
    {
    	return status('STUB_METHOD');
    }
    
    
    /**
     * @desc Pobiera listę dochodow ze wskazanego budzetu
     * @param int
     * @return Incomes
     * @example 1
     * @logged true
     */
    public function GetIncomes($budgetId)
    {
    	if ($this->DoesBudgetExist($budgetId)){
    		if ($s = $this->mysqli->prepare("SELECT ID_Przychodu, nazwa, kwota, data FROM Przychody where ID_Budzetu = ?")) {
    	    			$s->bind_param('i', $budgetId);
    	    			$s->execute();
    	    			$s->bind_result($ID_Przychodu, $nazwa, $kwota, $data);
    	    			$arr = array();
    	    			while ( $s->fetch() ) {
    	    				$row = array('ID_Przychodu' => $ID_Przychodu,'nazwa' => $nazwa,'kwota' => $kwota, 'data'=> $data);
    	    				$arr[] = $row;
    	    			}
    	    			return array('count' =>  $s->num_rows,
    	    					'incomes' => $arr);
    		}
    		else
    			return status('CANNOT_GET_INCOMES');
    	}
    	else
    		return status('NO_SUCH_BUDGET');
    }
    
    
    /**
     * @desc Dodaje nowy przychod
     * @param int, String, double, int
     * @return boolean
     * @example 3, Wypłata listopad, 1600, 1
     * @logged true
     */
    public function AddIncome($budgetId,$name,$amount,$incomeCategory)
    {
    	if (!$this->DoesBudgetExist($budgetId))
    		return status('NO_SUCH_BUDGET');
    	else{
    		if ($s = $this->mysqli->prepare("INSERT INTO Przychody (ID_Budzetu,ID_KatPrzychodu,kwota,nazwa) values (?, ?, ?, ?);")) {
    			$s->bind_param('iids',$budgetId,$incomeCategory,$amount,$name);
    			$s->execute();
    			$s->bind_result();
    			return status('INCOME_ADDED');
    		}
    		else
    			return status('INCOME_NOT_ADDED');
    	}
    }
    
    /**
     * @desc Pobiera listę ostatnich operacji ze wskazanego budzetu
     * @param int, String, int
     * @return Activities
     * @example 1, DESC, 20
     * @logged true
     */
    public function GetRecentActivities($budgetId, $order = "DESC ", $limit = 20)
    {	
    	if (empty($order))
    		$order = "DESC";
    	if (empty($limit))
    		$limit = 20;    	 	
    	
   		$sql = $this->OrderBy('(SELECT "przychod" AS  "rodzaj",ID_Przychodu as ID_Zdarzenia, nazwa, kwota, data FROM Przychody WHERE ID_Budzetu = ?) UNION
    (SELECT  "wydatek" AS  "rodzaj",`ID_Wydatku` as ID_Zdarzenia, nazwa, kwota, W.data FROM Wydatki W JOIN Produkty P ON W.ID_Produktu = P.ID_Produktu WHERE ID_Budzetu = ?)'
    	, "data", $order);   		
		$sql = $this->Limit($sql,$limit);
    	if ($this->DoesBudgetExist($budgetId)){
    		if ($s = $this->mysqli->prepare($sql)) {
    			$s->bind_param('ii', $budgetId,$budgetId);
    			$s->execute();
    			$s->bind_result($rodzaj,$ID_Zdarzenia, $nazwa, $kwota, $data);
    			$arr = array();
    			while ( $s->fetch() ) {
    				$row = array('rodzaj' => $rodzaj,'ID_Zdarzenia' => $ID_Zdarzenia, 'nazwa' => $nazwa,'kwota' => $kwota, 'data'=> $data);
    				$arr[] = $row;
    			}
    			return array('count' =>  $s->num_rows,
    					'activities' => $arr);
    		}
    		else
    			return status('CANNOT_GET_ACTIVITIES');
    	}
    	else
    		return status('NO_SUCH_BUDGET');
    }
   
    
    /**
     * @desc Pobiera sume przychodow
     * @param int
     * @return double
     * @example 1
     * @logged true
     */
    public function GetIncomesSum($budgetId)
    { 	
    	if ($this->DoesBudgetExist($budgetId)){
    		if ($s = $this->mysqli->prepare("SELECT sum(kwota) as suma FROM Przychody where ID_Budzetu = ?")) {
    		$s->bind_param('i', $budgetId);
    		$s->execute();
    		$s->bind_result($suma);
    		$s->store_result();
    		$s->fetch();
    		if ($s->num_rows > 0)
    			return round($suma,2);
    		else
    			return status('NO_INCOMES_ADDED');
    		}
    	}
    	return status('NO_SUCH_BUDGET');
    }
    
    /**
     * @desc Pobiera sume wydatkow
     * @param int
     * @return double
     * @example 1
     * @logged true
     */
    public function GetExpensesSum($budgetId)
    {    	
    	if ($this->DoesBudgetExist($budgetId)){
    		if ($s = $this->mysqli->prepare("SELECT sum(kwota) as suma FROM Wydatki where ID_Budzetu = ?")) {
    			$s->bind_param('i', $budgetId);
    			$s->execute();
    			$s->bind_result($suma);
    			$s->store_result();
    			$s->fetch();
    			if ($s->num_rows > 0)
    				return round($suma,2);
    			else
    				return status('NO_EXPENSES_ADDED');
    		}
    	}
    	else
    		return status('NO_SUCH_BUDGET');
    }
    
    
    
    /**
     * @desc Pobiera bilans danego budzetu
     * @param int
     * @return double
     * @example 1
     * @logged true
     */
    public function GetBudgetBilans($budgetId)
    {
    	if ($this->DoesBudgetExist($budgetId)){
			$bilans = 0;
        	$incomes = $this->GetIncomesSum($budgetId);
        	$expenses = $this->GetExpensesSum($budgetId);
			$bilans = $incomes - $expenses;
    		return round($bilans,2);
    	}
    	else
    		return status('NO_SUCH_BUDGET');
    }
    
    
    /**
     * @desc Pobiera powiadomienia
     * @param boolean
     * @return Notificatons
     * @example true
     * @logged true
     */
    public function GetNotifications($all)
    {   
        $all = $all == 'true' ? 1 : 0; 
        $all = $all ? 1 : 0;
        if ($s = $this->mysqli->prepare("SELECT `ID_Powiadomienia`,`ID_Zdarzenia`,`typ`,`tekst`,`data`,`przeczytane` FROM Powiadomienia WHERE `ID_Uzytkownika` = ? AND (przeczytane = 1 OR przeczytane <> ?)")) {
                $s->bind_param('ii', $this->userId,$all);
                $s->execute();
                $s->bind_result($ID_Powiadomienia,$ID_Zdarzenia,$typ,$tekst,$data,$przeczytane);
                $arr = array();
                while ( $s->fetch() ) {
                    $row = array('ID_Powiadomienia' => $ID_Powiadomienia,'ID_Zdarzenia' => $ID_Zdarzenia,'typ' => $typ,'tekst' => $tekst,'data' =>$data,'przeczytane' => $przeczytane);
                    $arr[] = $row;
                }
                return array('count' =>  $s->num_rows,
                             'notifications' => $arr);
            }
            else
                return status('NO_NOTIFICATIONS');
    }
    
    /**
     * @desc Oznacza powiadomienie jako przeczytane
     * @param int
     * @return void
     * @example 1
     * @logged true
     */
    public function MarkNotificationAsRead($notificationId)
    {
        if ($this->DoesNotificationExist($notificationId)){
            if ($s = $this->mysqli->prepare("UPDATE Powiadomienia SET przeczytane = 1 where ID_Powiadomienia = ?")) {
                $s->bind_param('i',$notificationId);
                $s->execute();
                return status('NOTIFICATION_MARKED');
            }
            else 
                return status('NOTIFICATION_NOT_MARKED');
        }
        else
            return status('NO_SUCH_NOTIFICATION');
    }
    
    
    private function MarkNotificationAsAdded($notificationId)
    {
        if ($this->DoesNotificationExist($notificationId)){
            if ($z = $this->mysqli->prepare("UPDATE Powiadomienia SET dodane = 1 where ID_Powiadomienia = ?")) {
                $z->bind_param('i',$notificationId);
                $z->execute();
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }
    

    
    
    private function AddNotification($eventId, $eventType, $text, $date)
    {
        if ($s = $this->mysqli->prepare("INSERT INTO Powiadomienia (`ID_Uzytkownika`, `ID_Zdarzenia`,`typ`,`tekst`,`data`,`przeczytane`) values (?, ?, ?, ?, ?, 0);")) {
            $s->bind_param('iisss',$this->userId,$eventId,$eventType, $text, $date);
            $s->execute();
            $s->bind_result();
            return true;
        }
        else
            return false;       
    }
    
    private function GetNotAddedNotifications()
    {
        if ($y = $this->mysqli->prepare("SELECT `ID_Powiadomienia`,`ID_Zdarzenia`,`typ`,`tekst`,`data`,`przeczytane`,`dodane` FROM Powiadomienia WHERE `ID_Uzytkownika` = ? AND DATE(data) <= DATE(NOW()) AND przeczytane = 0 and dodane = 0")) {
            $y->bind_param('i', $this->userId);
            $y->execute();
            $y->bind_result($ID_Powiadomienia,$ID_Zdarzenia,$typ,$tekst,$data,$przeczytane,$dodane);
            $arr = array();
            while ( $y->fetch() ) {
                $row = array('ID_Powiadomienia' => $ID_Powiadomienia,'ID_Zdarzenia' => $ID_Zdarzenia,'typ' => $typ,'tekst' => $tekst,'data' =>$data,'przeczytane' => $przeczytane,'dodane' => $dodane);
                $arr[] = $row;
            }
            if ($y->num_rows > 0)
                return $arr;
        }
    }
    
    /**
     * @desc Sprawdza wszystkie powiadomienia - data równa lub mniejsza niz dzisiaj
     * @param void
     * @return Notificatons
     * @example void
     * @logged true
     */
    public function CheckNotifications()
    {
        $data = $this->GetNotAddedNotifications();
        $added = 0;
        foreach ($data as $value) {
            $dodane = ($value['dodane'] ? 1 : 0);
            if (!$dodane){
                if ($value['typ'] == "wydatek"){
                    $this->AddScheduledExpenseToExpenses($value['ID_Zdarzenia']);
                    $this->MarkNotificationAsAdded($value['ID_Powiadomienia']);
                    $added++;
                }
                else if ($value['typ'] == "dochod"){
                    $this->AddScheduledIncomeToIncomes($value['ID_Zdarzenia']);
                    $this->MarkNotificationAsAdded($value['ID_Powiadomienia']);
                    $added++;
                }
            }
        }  
        if ($added > 0)
            return status('UPDATED');
        else 
            return status('NOT_UPDATED');      
    }
    
    
    /**
     * @desc Dodaje zaplanowany wydatek do listy zaplanowanych wydatkow
     * @param int,String, double, String
     * @return boolean
     * @example 1, paliwo, 100, 2013-12-20
     * @logged true
     */
    public function AddScheduledExpense($budgetId,$productName, $amount, $date)
    {
        if (!$this->GetProductByName($productName))
    		$this->AddProduct($productName, 1);
        $productId = $this->GetProductByName($productName);
        
        if (!$this->GetRecentScheduledExpense($budgetId, $productId, $amount, $date)){
            if ($s = $this->mysqli->prepare("INSERT INTO `PlanowanyWydatek` (`ID_Budzetu`,`ID_Produktu`,`kwota`,`data`) VALUES (?, ?, ?, ?);")) {
                $s->bind_param('iids',$budgetId,$productId, $amount, $date);
                $s->execute();
                $s->bind_result();
                $scheduledExpenseId = $this->GetRecentScheduledExpense($budgetId,$productId, $amount, $date);
                $this->AddNotification($scheduledExpenseId, "wydatek", "Dodano zaplanowany wydatek: ".$productName." o wartosci ".$amount."zl", $date);
                return status('SCHEDULED_EXPENSE_ADDED');
            }
            else
                return status('SCHEDULED_EXPENSE_NOT_ADDED');
        }
        else
            return status('SCHEDULED_EXPENSE_ALREADY_EXISTS');
    }
    
    /**
     * @desc Dodaje zaplanowany wydatek do wydatkow
     * @param int
     * @return boolean
     * @example 10
     * @logged true
     */
    private function AddScheduledExpenseToExpenses($scheduledExpenseId)
    {   
        $scheduledExpenseId = 2;
        if ($x = $this->mysqli->prepare("SELECT ID_Budzetu,ID_Produktu,kwota,data FROM PlanowanyWydatek where ID_PlanowanegoWydatku = ?")) {
            $x->bind_param('i', $scheduledExpenseId);
            $x->execute();
            $x->bind_result($ID_Budzetu,$ID_Produktu,$kwota,$data);
            $x->store_result();
            $x->fetch();
            
            if ($x->num_rows > 0){
            	if ($s = $this->mysqli->prepare("INSERT INTO Wydatki (ID_Budzetu,ID_Produktu,kwota,data) values (?, ?, ?, ?);")) {
            		$s->bind_param('iids',$ID_Budzetu,$ID_Produktu,$kwota,$data);
            		$s->execute();
            		$x->bind_result();
            		return true;
            	}
            }
            else
                return false;
        }
        return false;
    }
    
    
    
    /**
     * @desc Pobiera zaplanowane wydatki (z przyszlosci)
     * @param int
     * @return ScheduledExpenses
     * @example 1
     * @logged true
     */
    public function GetScheduledExpenses($budgetId)
    {
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("SELECT `ID_Budzetu`,`ID_PlanowanegoWydatku`,P.nazwa, KP.nazwa,`kwota`,PW.`data` FROM `PlanowanyWydatek` PW join `Produkty` P on PW.`ID_Produktu` = P.`ID_Produktu` join `KategorieProduktow` KP on P.`ID_KatProduktu` = KP.`ID_KatProduktu` WHERE `ID_Budzetu` = ?  AND DATE(PW.data) > DATE(NOW())")) {
                $s->bind_param('i', $budgetId);
                $s->execute();
                $s->bind_result($ID_Budzetu,$ID_PlanowanegoWydatku,$produkt, $kategoria,$kwota,$data);
                $arr = array();
                while ( $s->fetch() ) {
                    $row = array('ID_Budzetu' => $ID_Budzetu, 'ID_PlanowanegoWydatku' => $ID_PlanowanegoWydatku, 'produkt' => $produkt, 'kategoria' =>  $kategoria, 'kwota' => $kwota, 'data' => $data);
                    $arr[] = $row;                     
                }
                if ($s->num_rows > 0)
                    return array('count' =>  $s->num_rows,
                            'scheduled_expenses' => $arr);
                else
                    return status('NO_SCHEDULED_EXPANSES');
            }
            else
                return status('NO_SCHEDULED_EXPANSES');
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    
    
    /**
     * @desc Dodaje zaplanowany przychod
     * @param int,String, String, double, String
     * @return boolean
     * @example 1, pensja grudzien, pensja, 4500, 2013-12-10
     * @logged true
     */
    public function AddScheduledIncome($budgetId, $name, $categoryName, $amount, $date)
    {
        if (!$this->GetIncomeCategoryByName($categoryName))
            $categoryId = 1; // Ustaw 1 - czyli inna
        else    
            $categoryId = $this->GetIncomeCategoryByName($categoryName);
        
        if (!$this->GetRecentScheduledIncome($budgetId, $name, $categoryId, $amount, $date)){
            if ($s = $this->mysqli->prepare("INSERT INTO `PlanowanyDochod` (`ID_Budzetu`,`ID_KatPrzychodu`,`nazwa`,`kwota`,`data`) VALUES (?, ?, ?, ?, ?);")) {
                $s->bind_param('iisds',$budgetId,$categoryId,$name, $amount, $date);
                $s->execute();
                $s->bind_result();
                $scheduledIncomeId = $this->GetRecentScheduledIncome($budgetId, $name, $categoryId, $amount, $date);
                $this->AddNotification($scheduledIncomeId, "dochod", "Dodano zaplanowany dochod: ".$name." o wartosci ".$amount."zl", $date);
                return status('SCHEDULED_INCOME_ADDED');
            }
            else
                return status('SCHEDULED_INCOME_NOT_ADDED');
        }
        else
            return status('SCHEDULED_INCOME_ALREADY_EXISTS');
    }
    
    /**
     * @desc Dodaje zaplanowany przychod do przychodow
     * @param int
     * @return boolean
     * @example 10
     * @logged true
     */
    private function AddScheduledIncomeToIncomes($scheduledIncomeId)
    {
        if ($x = $this->mysqli->prepare("SELECT `ID_Budzetu`,PD.`ID_KatPrzychodu`,`ID_PlanowanegoDochodu`,PD.nazwa, KP.nazwa,`kwota`,PD.`data` FROM `PlanowanyDochod` PD join `KategoriePrzychodow` KP on PD.`ID_KatPrzychodu` = KP.`ID_KatPrzychodu` WHERE ID_PlanowanegoDochodu = ?")) {
            $x->bind_param('i', $scheduledIncomeId);
            $x->execute();
            $x->bind_result($ID_Budzetu,$ID_KatPrzychodu,$ID_PlanowanegoDochodu,$nazwa, $kategoria,$kwota,$data);
            $x->store_result();
            $x->fetch();
            $this->Debug($x);
            if ($x->num_rows > 0){
                if ($s = $this->mysqli->prepare("INSERT INTO Przychody (ID_Budzetu,ID_KatPrzychodu,kwota,nazwa,data) values (?, ?, ?, ?, ?);")) {
                    $s->bind_param('iidss',$ID_Budzetu,$ID_KatPrzychodu,$kwota,$nazwa, $data);
                    $s->execute();
                    $s->bind_result();
                    $this->Debug($s);
                    return true;
                }
            }
            else
                return false;
        }
        return false;
    }

    /**
     * @desc Pobiera zaplanowane przychody (z przyszlosci)
     * @param int
     * @return ScheduledIncomes
     * @example 1
     * @logged true
     */
    public function GetScheduledIncomes($budgetId)
    {
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("SELECT `ID_Budzetu`,`ID_PlanowanegoDochodu`,PD.nazwa, KP.nazwa,`kwota`,PD.`data` FROM `PlanowanyDochod` PD join `KategoriePrzychodow` KP on PD.`ID_KatPrzychodu` = KP.`ID_KatPrzychodu` WHERE `ID_Budzetu` =  ? AND DATE(PD.data) > DATE(NOW())")) {
                $s->bind_param('i', $budgetId);
                $s->execute();
                $s->bind_result($ID_Budzetu,$ID_PlanowanegoDochodu,$nazwa, $kategoria,$kwota,$data);
                $arr = array();
                while ( $s->fetch() ) {
                    $row = array('ID_Budzetu' => $ID_Budzetu, 'ID_PlanowanegoDochodu' => $ID_PlanowanegoDochodu, 'nazwa' => $nazwa, 'kategoria' =>  $kategoria, 'kwota' => $kwota, 'data' => $data);
                    $arr[] = $row;
                }
                if ($s->num_rows > 0)
                    return array('count' =>  $s->num_rows,
                            'scheduled_incomes' => $arr);
                else
                    return status('NO_SCHEDULED_INCOMES');
            }
            else
                return status('NO_SCHEDULED_INCOMES');
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    private function ExpenseCategories(){
        $categories = array(
                'jedzenie' => array( 2,19,20,21,22,23,53,54,55,56,57,84),
                'rodzina i znajomi' => array( 37,59,61,62,59,96),
                'dom' => array( 17,18,35,36,51,60,64,69,72,73,74,90),
                'oplaty' => array( 8,9,10,27,82,85,86,87),
                'transport' => array( 15,30,31,42,98),
                'podroze' => array( 13,14),
                'rozrywka' => array(32,34,39,40,41,43,44,45,46,47,48,49,50,65,83,88),
                'rtv i agd' => array( 25,26,28),
                'zdrowie i uroda' => array( 5,12,33,68,70,71,93,94),
                'odziez' => array(3,4),
                'uslugi' => array( 80,81,92),
                'inne' => array( 1,7,11,29,38,52,58,63,66,67,75,76,77,78,79,89,91,97)
        );
        return $categories;
    }
    
    private function GetSumOfExpensesFromMonth($budgetId,$month,$year){
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("SELECT SUM(  `kwota` ) AS suma
                                            FROM  `Wydatki` W
                                            JOIN Produkty P ON P.`ID_Produktu` = W.`ID_Produktu`
                                            WHERE  `ID_Budzetu` = ?
                                            AND MONTH( W.data ) = ?
                                            AND YEAR( W.data ) = ?")){
                                                $s->bind_param('iii', $budgetId,$month,$year);
                                                $s->execute();
                                                $s->bind_result($suma);
                                                $s->store_result();
                                                $s->fetch();
                                                if (is_null($suma))
                                                    return 0;
                                                return $suma;
            }
            return 0;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    private function GetSumOfExpensesFromMonthByCategory($budgetId,$month,$year,$category){
        $cat = "(";
        foreach ($category as $value) {
            $cat = $cat.$value.",";
        }
        $cat = substr($cat,0,-1);
        $cat .= ")";
        $sql = "SELECT SUM(  `kwota` ) AS suma FROM  `Wydatki` W JOIN Produkty P ON P.`ID_Produktu` = W.`ID_Produktu`
                WHERE  `ID_Budzetu` = ? AND MONTH( W.data ) = ? AND YEAR( W.data ) = ? and P.`ID_KatProduktu` in ";
        if ($this->DoesBudgetExist($budgetId)){
    
            if ($s = $this->mysqli->prepare($sql.$cat)){
    
                $s->bind_param('iii', $budgetId,$month,$year);
                $s->execute();
                $s->bind_result($suma);
                $s->store_result();
                $s->fetch();
                if (is_null($suma))
                    return 0;
                return $suma;
            }
            return 0;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
 
    /**
     * @desc Pobiera dane do wykresu kolowego z danego miesiaca dla wydatkow
     * @param int, String
     * @return PieChart
     * @example 1, 2013-12-10
     * @logged true
     */
    public function GetExpensesPieChart($budgetId,$date)
    { 
        $date = strtotime($date);
        $month = date("n",$date);
        $year = date("Y",$date);
        if ($this->DoesBudgetExist($budgetId)){
            $sum = $this->GetSumOfExpensesFromMonth($budgetId,$month,$year);
            if ($sum > 0){
                $arr = array();
                foreach ($this->ExpenseCategories() as $category => $val) { 
                    $sum_cat = $this->GetSumOfExpensesFromMonthByCategory($budgetId,$month,$year,$val);
                    $arr[] = array('kategoria' => $category,
                                    'suma' => $sum_cat,
                                    'procent' => ($sum_cat/$sum));
                }
                return $arr;
            }
                return status('NO_EXPENSES_IN_MONTH');
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    

    /**
     * @desc Pobiera dane do wykresu z ustatnich $Months miesiecy z wydatkow z danej kategorii
     * @param int, int, String
     * @return BarChart
     * @example 1, 6, inne
     * @logged true
     */
    public function GetExpenseCategoryChart($budgetId,$months,$categoryName)
    {
        if ($this->DoesBudgetExist($budgetId)){
            $arr = array();
            $categories = $this->ExpenseCategories();
            $category = $categories[$categoryName];
            foreach ($this->getMonths($months) as $date) {
                $sum_cat = $this->GetSumOfExpensesFromMonthByCategory($budgetId,$date['month'],$date['year'],$category);
                $arr[] = array('kategoria' => $categoryName,
                        'suma' => $sum_cat,
                        'month' => $date['month'],
                        'year' => $date['year']);
            }
            return $arr;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    private function IncomeCategories(){
        $categories = array(
                'praca'=> array(2,4,11),
                'rodzina i znajomi'=> array( 12,13),
                'handel'=> array( 9,15),
                'zlecenia'=> array( 4,10),
                'inne'=> array( 1,3,5,6,7,8,14,16,17,18)
        );
        return $categories;
    }
    
    private function GetSumOfIncomesFromMonth($budgetId,$month,$year){
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("SELECT SUM(  `kwota` ) AS suma FROM  `Przychody` P JOIN KategoriePrzychodow KP ON P.`ID_KatPrzychodu`=KP.`ID_KatPrzychodu`
                                            WHERE  `ID_Budzetu` = ?
                                            AND MONTH( P.data ) = ?
                                            AND YEAR( P.data ) = ?")){
                                                $s->bind_param('iii', $budgetId,$month,$year);
                                                $s->execute();
                                                $s->bind_result($suma);
                                                $s->store_result();
                                                $s->fetch();
                                                if (is_null($suma))
                                                    return 0;
                                                return $suma;
            }
            return 0;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    private function GetSumOfIncomesFromMonthByCategory($budgetId,$month,$year,$category){
        $cat = "(";
        foreach ($category as $value) {
            $cat = $cat.$value.",";
        }
        $cat = substr($cat,0,-1);
        $cat .= ")";
        $sql = "SELECT SUM(  `kwota` ) AS suma FROM  `Przychody` P JOIN KategoriePrzychodow KP ON P.`ID_KatPrzychodu`=KP.`ID_KatPrzychodu`
                WHERE  `ID_Budzetu` = ? AND MONTH( P.data ) = ? AND YEAR( P.data ) = ? and P.`ID_KatPrzychodu` in ";
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare($sql.$cat)){
                $s->bind_param('iii', $budgetId,$month,$year);
                $s->execute();
                $s->bind_result($suma);
                $s->store_result();
                $s->fetch();
              
                if (is_null($suma))
                    return 0;
                return $suma;
            }
            return 0;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    
    /**
     * @desc Pobiera dane do wykresu kolowego z danego miesiaca dla przchodow
     * @param int, String
     * @return PieChart
     * @example 1, 2013-12-10
     * @logged true
     */
    public function GetIncomesPieChart($budgetId,$date)
    {
        $date = strtotime($date);
        $month = date("n",$date);
        $year = date("Y",$date);
        if ($this->DoesBudgetExist($budgetId)){
            $sum = $this->GetSumOfIncomesFromMonth($budgetId,$month,$year);
            if ($sum > 0){
                $arr = array();
                foreach ($this->IncomeCategories() as $category => $val) {
                    $sum_cat = $this->GetSumOfIncomesFromMonthByCategory($budgetId,$month,$year,$val);
                    $arr[] = array('kategoria' => $category,
                            'suma' => $sum_cat,
                            'procent' => ($sum_cat/$sum));
                }
                return $arr;
            }
            return status('NO_INCOMES_IN_MONTH');
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    /**
     * @desc Pobiera dane do wykresu z ustatnich Months miesiecy z przchodow z danej kategorii
     * @param int, int, String
     * @return BarChart
     * @example 1, 6, inne
     * @logged true
     */
    public function GetIncomesCategoryChart($budgetId,$months,$categoryName)
    {
        if ($this->DoesBudgetExist($budgetId)){
            $arr = array();
            $categories = $this->IncomeCategories();
            $category = $categories[$categoryName];
            foreach ($this->getMonths($months) as $date) {
                $sum_cat = $this->GetSumOfIncomesFromMonthByCategory($budgetId,$date['month'],$date['year'],$category);
                $arr[] = array('kategoria' => $categoryName,
                        'suma' => $sum_cat,
                        'month' => $date['month'],
                        'year' => $date['year']);
            }
            return $arr;
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
    
    private function getMonths($months){
        $currYear = date("Y");
        $currMonth = date("n");
        if ($months > $currMonth){
            $diff = floor($months / 12) + ($months % 12 > 0 ? 1 : 0);
            $begYear = $currYear - $diff;
            $begMonth = 12 * $diff + $currMonth - $months + 1;      
        }
        else
        {
        	$begYear = $currYear;
        	$begMonth = $currMonth - $months + 1;
        }
        
        $dates = array();
        while ( $begMonth <=$currMonth && $begYear <= $currYear )
        {
        	$dates[] = array('year' => $begYear, 'month' => $begMonth);
        	$begMonth++;
            if ($begMonth == 13)
            {
            	$begMonth = 1;
            	$begYear++;
            }
        }
        return $dates;
    }
    
    
    private function GetCurrnetLimits($budgetId)
    {
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("SELECT  ID_Limitu,`limit`,sum(kwota) as suma, `limit` - SUM( kwota ) AS  'roznica', 1 - (SUM( kwota) / `limit`) as procent, KP.nazwa
                        FROM Limity L
                        JOIN Produkty P ON P.ID_KatProduktu = L.ID_KatProduktu
                        JOIN Wydatki W ON W.ID_Produktu = P.ID_Produktu
                        JOIN KategorieProduktow KP ON KP.ID_KatProduktu = P.ID_KatProduktu
                        WHERE L.`ID_Budzetu` = ?
                        AND YEAR(L.data) = YEAR(NOW( )) 
                        AND MONTH(L.data) = MONTH(NOW( )) 
                        AND YEAR( W.data) = YEAR(NOW( ) ) 
                        AND MONTH(W.data) = MONTH(NOW( )) 
                        GROUP BY P.`ID_KatProduktu`")) {
                $s->bind_param('i', $budgetId);
                $s->execute();
                $s->bind_result($ID_Limitu,$limit,$suma, $roznica, $procent, $nazwa);
                $arr = array();
                while ( $s->fetch() ) {
                    $row = array('ID_Limitu' => $ID_Limitu, 'limit' => $limit, 'suma' =>  $suma, 'roznica' => $roznica, 'procent' => $procent, 'nazwa' => $nazwa);
                    $arr[] = $row;
                }
                if ($s->num_rows > 0)
                    return $arr;
                else
                    return false;
            }
            else
                return false;
        }
        else
            return false;
    }
    
    
    
    /**
     * @desc Pobiera listę limitow wydatkow z danego miesiaca
     * @param int
     * @return Limits
     * @example 1
     * @logged true
     */
    public function GetLimits($budgetId)
    {
        $limits = $this->GetCurrnetLimits($budgetId);
        if ($limit != false)
        {
            return array('count' => count($limits), 'limits' => $limits);        
        }
        else return status('NO_LIMITS');
    }
    
    
    
    /**
     * @desc Dodaje limit
     * @param int,int, double
     * @return boolean
     * @example 1, 4, 1000
     * @logged true
     */
    public function AddLimit($budgetId, $categoryId, $limit)
    {
        if ($this->DoesBudgetExist($budgetId)){
            if ($s = $this->mysqli->prepare("INSERT INTO `Limity`(`ID_Budzetu`, `ID_KatProduktu`, `limit`, `data`) VALUES (?,?,?,NOW());")) {
                $s->bind_param('iid',$budgetId,$categoryId,$limit);
                $s->execute();
                $s->bind_result();    
                return status('LIMIT_ADDED');
            }
            else
                return status('LIMIT_NOT_ADDED');
        }
        else
            return status('NO_SUCH_BUDGET');
    }
    
   
    /**
     * @desc Sprawdza wszystkie dodane limity - dodaje powiadomienie o przekroczeniu limitu 
     * @param int 
     * @return boolean
     * @example 1
     * @logged true
     */
    public function CheckLimits($budgetId)
    {
        $limits = $this->GetCurrnetLimits($budgetId);
        $data = $this->GetNotAddedNotifications();
        $previuoslyAdded = array();
        foreach ($data as $d) {
        	if ($d['typ'] == 'limit')
        	    $previuoslyAdded[] = $d['ID_Zdarzenia'];
        }
        $warnings = 0;
        if ($limits != false)
        {
            foreach ($limits as $limit) {
                $procent = $limit['procent'];
                if ($procent <= 0.1 && $procent >=0)
                    $msg = "Zbilizasz sie do limitu wydatkow w kategorii ".$limit['nazwa'].". Wydales juz ".$limit['suma']." z ". $limit['limit']." zł";
                if ($procent <= 0)
                    $msg = "Wlasnie przekroczyles limit wydatkow w kategorii ".$limit['nazwa'].". Wydales ".$limit['suma']." z zaplanowanych ". $limit['limit']." zł";

                if (isset($msg) && !in_array($limit['ID_Limitu'],$previuoslyAdded)){
                    $this->AddNotification($limit['ID_Limitu'], "limit",$msg,date("Y-m-d"));
                    $warnings++;
                }
                    
            }
            if ($warnings > 0)
                return status('NEW_NOTIFICATIONS_ADDED');
            else
                return status('NO_NEW_NOTIFICATIONS');
        }
        return status('NO_LIMITS');
    }
    
    /**
     * @desc Do testowania
     * @param int
     * @return void
     * @example 2
     * @logged true
     */
    public function Test($id)
    {
    	$this->AddScheduledExpenseToExpenses($id);
        
    }
    
    //TODO raporty pokaz wydatki wg produktow - x dni, tydzien, x tygodni, miesiac, x miesiecy, rok, caly czas
    //TODO raporty pokaz przychodu wg produktow - x dni, tydzien, x tygodni, miesiac, x miesiecy, rok, caly czas
    //TODO dodawanie zakupow - nazwa i sklep oraz lista produktow dwie metody- dodaje zakupy, dodaj wydatki do zakupow  
    //TODO dodawanie modyfikowanie i usuwanie planowanych wydatkow i przychodow
    //TODO dodawanie zleceń stałych 
    //TODO dodawnie powiadomień - przy dodaniu zlecenia stałego dodaj powiadomienie

    // W drugiej kolejnosci
    //TODO usuwanie i edycja wydatkow z budzetu
    //TODO zrobic slownik wartosc z paragonu - id produktu na liscie
    //TODO zmiana danych uzytkownika
    //TODO modyfikowanie i usuwanie przychodow

}
?>