<?php
class Tools
{
    static function connect(
        $host = "localhost:3307",
        $user = "root",
        $pass = "123456",
        $dbname = "shop"
    ) {
        $cs = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8;';
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
        );
        try {
            $pdo = new
                PDO($cs, $user, $pass, $options);
            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    static function register($name, $pass, $imagepath)
    {
        $name = trim($name);
        $pass = trim($pass);
        $imagepath = trim($imagepath);
        if ($name == "" || $pass == "") {
            echo "<h3/><span style='color:red;'> Fill All Required Fields!</span><h3/>";
            return false;
        }
        if (strlen($name) < 3 || strlen($name) > 30 || strlen($pass) < 3 || strlen($pass) > 30) {
            echo "<h3/><span style='color:red;'> Values Length Must Be Between 3 And 30!</span><h3/>";
            return false;
        }
        Tools::connect();
        $customer = new Customer($name, $pass, $imagepath);
        $err = $customer->intoDb();
        if ($err) {
            if ($err == 1062)
                echo "<h3/><span style='color:red;'> This Login Is Already Taken!</span><h3/>";
            else
                echo "<h3/><span style='color:red;'> Error code:" . $err . "!</span><h3/>";
            return false;
        }
        return true;
    }
}
class Customer
{
    protected $id; //user id
    protected $login;
    protected $pass;
    protected $roleid;
    protected $discount; //customer's personal discount
    protected $total; //total ammount of purchases
    protected $imagepath; //path to the image
    function __construct($login, $pass, $imagepath, $id = 0)
    {
        $this->login = $login;
        $this->pass = $pass;
        $this->imagepath = $imagepath;
        $this->id = $id;
        $this->total = 0;
        $this->discount = 0;
        $this->roleid = 2;
    }
    function intoDb()
    {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("INSERT INTO Customers (login,pass,roleid,discount,total,imagepath) VALUES (:login,:pass,:roleid,:discount,:total,:imagepath)");
            $ar = (array)$this;
            array_shift($ar);
            $ps->execute($ar);
        } catch (PDOException $e) {
            $err = $e->getMessage();
            if (substr($err, 0, strrpos($err, ":")) == 'SQLSTATE[23000]:Integrity constraint violation')
                return 1062;
            else
                return $e->getMessage();
        }
    }
    static function fromDb($id)
    {
        $customer = null;
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare(("SELECT * FROM Customers WHERE id=?"));
            $res = $ps->execute(array($id));
            $row = $res->fetch();
            $customer = new Customer($row['login'], $row['pass'], $row['imagepath'], $row['id']);
            return $customer;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}

class Item
{
    public $id, $itemname, $catid, $pricein,
        $pricesale, $info, $rate,
        $imagepath, $action;
    function __construct(
        $itemname,
        $catid,
        $pricein,
        $pricesale,
        $info,
        $imagepath,
        $rate = 0,
        $action = 0,
        $id = 0
    ) {
        $this->id = $id;
        $this->itemname = $itemname;
        $this->catid = $catid;
        $this->pricein = $pricein;
        $this->pricesale = $pricesale;
        $this->info = $info;
        $this->rate = $rate;
        $this->imagepath = $imagepath;
        $this->action = $action;
    }
    function intoDb()
    {
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare("INSERT INTO Items (itemname, catid, pricein, pricesale, info, rate, imagepath, action) VALUES (:itemname, :catid, :pricein, :pricesale, :info, :rate, :imagepath, :action)");
            $ar = (array)$this;
            array_shift($ar);
            $ps->execute($ar);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    static function fromDb($id)
    {
        $customer = null;
        try {
            $pdo = Tools::connect();
            $ps = $pdo->prepare(("SELECT * FROM Items WHERE id=?"));
            $res = $ps->execute(array($id));
            $row = $res->fetch();
            $customer = new Item(
                $row['itemname'],
                $row['catid'],
                $row['pricein'],
                $row['pricesale'],
                $row['info'],
                $row['imagepath'],
                $row['rate'],
                $row['action'],
                $row['id']
            );
            return $customer;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
