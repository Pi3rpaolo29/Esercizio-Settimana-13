<?php
namespace Site {
    use PDO;

    // Questa classe fa CRUD su database
    class Database
    {
        // Classe con pattern Singleton
        private PDO $conn;
        private static ?Database $instance = null;
        //Il construct della classe
        private function __construct(array $config)
        {
            // 'mysql:host=localhost; port=3306; dbname=pdotestsettimanale
            $this->conn = new PDO(
                $config['driver'] . ":host=" . $config['host'] . "; port=" . $config['port'] . "; dbname=" . $config['database'] . ";",
                $config['user'],
                $config['password']
            );
            $this->createTableUser();
        }
        //Pattern Singleton che crea un solo db se non esiste altrimenti ritorna il db esistente
        public static function getInstance(array $config)
        {
            if (!static::$instance) {
                static::$instance = new Database($config);
            }
            return static::$instance;
        }


        //Creo la Tabella se non esiste
        private function createTableUser()
        {
            $query = 'CREATE TABLE IF NOT EXISTS User ( 
                    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    email VARCHAR(255) NOT NULL UNIQUE , 
                    password VARCHAR(100) NOT NULL 
                )';

            // Preparazione della query
            $statement = $this->conn->prepare($query);

            // Esecuzione della query
            $statement->execute();
        }


        //Aggiungo una row nella linea utente
        public function createUser($email, $password)
        {
            //IGNORE ignora le righe con chiavi duplicate
            $query = "INSERT IGNORE INTO User (email, password) VALUES (:email, :password)";
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Preparazione della query
            $statement = $this->conn->prepare($query);

            //Sostituisco i valori della query con quelli delle variabili
            $statement->bindParam(':email', $email);
            $statement->bindParam(':password', $hashedPassword);

            // Esecuzione della query
            $statement->execute();
        }


        public function getUser($email)
        {
            // Leggo dati da una tabella
            $query = "SELECT * FROM user WHERE email = :email";
            $statement = $this->conn->prepare($query);

            //Sostituisco i valori della query con quelli delle variabili
            $statement->bindParam(':email', $email);

            // Execute the prepared statement
            $statement->execute();

            // Prende tuttele righe e le ritorna con un array associativo. Prende tutti gli utenti che trova e mi ritorna un array
            $user = $statement->fetchAll(PDO::FETCH_ASSOC);
            /* var_dump($user); */
            return $user[0];
        }

    }

    // Questa classe gestisce la sessione dell'utente
    class User
    {
        public static function register(array $userData, $database)
        {
            $email = User::emailVerification($userData['email']);
            $password = User::passwordVerification($userData['password']);
            var_dump($password);

            if ($email == false || $password == false) {
                $_SESSION['error'] = 'Email e Password errati!!!';
            } else if (strlen($email) < 3 || strlen($password) < 3) {
                $_SESSION['error'] = 'Email e Password errati!!!';
            } else {
                $database->createUser($email, $password);
            }
        }

        // Verifico e sanitizza il formato di una email
        private static function emailVerification($email)
        {
            $regexemail = '/^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/m';
            preg_match_all($regexemail, htmlspecialchars($email), $matchesEmail, PREG_SET_ORDER, 0);
            return $matchesEmail ? htmlspecialchars($email) : false;
        }

        // Verifico e sanitizza il formato di una password
        private static function passwordVerification($password)
        {
            $regexPass = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/';
            preg_match_all($regexPass, htmlspecialchars($password), $matchesPass, PREG_SET_ORDER, 0);
            //se la password rispetta l'espressione regolare della password scritta sopra ($regexPass) 
            //allora la funzione ritorna la password sanitised da XSS attacks 
            //altrimenti ritorna false
            return $matchesPass ? htmlspecialchars($password) : false;
        }

        public static function login(array $userData, $database)
        {
            $user = $database->getUser($userData['email']);

            if ($user && password_verify($userData['password'], $user['password'])) {
                $_SESSION['userLogin'] = $user;
                session_write_close();
                // Verifico se durante il login è stata messa la spunto sulla checkbox Remember me
                if (isset($userData['check'])) {
                    // Setting a cookie
                    setcookie("useremail", $user['email'], time() + 20 * 24 * 60 * 60);
                    setcookie("userpassword", $user['password'], time() + 20 * 24 * 60 * 60);
                }
                return 1;
            } else {
                $_SESSION['error'] = 'Email e Password errati!!!';
                return 0;
            }
        }

        public static function logout()
        {
            session_destroy(); // distruggo una sessione esistente
            setcookie("useremail", "", time() - 3600); // distruggo un cookie esistente
            setcookie("userpassword", "", time() - 3600); // distruggo un cookie esistente
            header('Location: http://localhost/Esercizio-Settimana-13/');
        }

        public static function areSessionOrCookiesSet()
        {
            return isset($_SESSION['userLogin']) || (isset($_COOKIE["useremail"]) && isset($_COOKIE["userpassword"]));
        }

        public static function getEmail()
        {
            return $_SESSION['userLogin']['email'];
        }
    }
}