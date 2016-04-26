<?php

require_once(CORE . '/class.symphony.php');

class DbBackup {

    private $filename = null;
    private $executed_command = null;
    private $failed = false;
    private $command_output = null;
    private $return_value = null;
    private $date_format = 'Y-m-d-H-i-s';

    public function __construct($filename = null) {
        $db_name = Symphony::Configuration()->get('db', 'database');
        if ($filename) {
            $this->filename = $filename;
        }
    }

    public function __toString() {
        return $this->file();
    }

    // Return the fully qualified file locator for the backup
    public function file() {
        return self::directory() . $this->filename;
    }

    // Return just the filename of the backup
    public function filename() {
        return $this->filename;
    }

    // Return the directory containing the backup file
    public static function directory() {
        return DOCROOT . Symphony::Configuration()->get('directory', 'db_manager');
    }

    // Generate a filename with current datetime and random codename
    public function generateFilename() {
        $db_name = Symphony::Configuration()->get('db', 'database');
        $adjectives = ["adventurous", "alert", "blushing", "colorful", "drab", "distinct", "elegant", "excited", "fancy", "glamorous", "gleaming", "graceful", "grotesque", "light", "magnificent", "misty", "muddy", "precious", "shiny", "sparkling", "spotless", "stormy", "strange", "unusual", "brainy", "busy", "cautious", "clever", "clumsy", "curious", "doubting", "famous", "frail", "gifted", "helpful", "innocent", "inquisitive", "modern", "mushy", "odd", "prickly", "puzzled", "real", "shy", "sleepy", "talented", "tame", "tender", "vast", "wandering", "wild", "wrong", "agreeable", "amused", "brave", "calm", "charming", "cheerful", "comfortable", "cooperative", "courageous", "delightful", "determined", "eager", "elated", "enchanting", "encouraging", "energetic", "enthusiastic", "excited", "exuberant", "faithful", "fantastic", "fine", "friendly", "funny", "gentle", "glorious", "happy", "hilarious", "jolly", "joyous", "kindly", "lively", "lucky", "perfect", "pleasant", "proud", "silly", "smiling", "splendid", "thankful", "thoughtful", "victorious", "vivacious", "witty", "zealous", "zany"];
        $animals = ["aardvark", "albatross", "alligator", "alpaca", "ant", "anteater", "antelope", "ape", "armadillo", "herd", "baboon", "badger", "barracuda", "bat", "bear", "beaver", "bee", "bison", "boar", "galago", "butterfly", "camel", "caribou", "cat", "caterpillar", "cattle", "chamois", "cheetah", "chicken", "chimpanzee", "chinchilla", "chough", "clam", "cobra", "cockroach", "cod", "cormorant", "coyote", "crab", "herd", "crocodile", "crow", "curlew", "deer", "dinosaur", "dog", "dolphin", "donkey", "dotterel", "dove", "dragonfly", "duck", "dugong", "dunlin", "eagle", "echidna", "eel", "elephant", "elk", "emu", "falcon", "ferret", "finch", "fish", "flamingo", "fly", "fox", "frog", "gaur", "gazelle", "gerbil", "giraffe", "gnat", "goat", "goose", "goldfish", "gorilla", "goshawk", "grasshopper", "grouse", "guanaco", "poultry", "herd", "gull", "hamster", "hare", "hawk", "hedgehog", "heron", "herring", "hippopotamus", "hornet", "horse", "human", "hummingbird", "hyena", "jackal", "jaguar", "jay", "jellyfish", "kangaroo", "koala", "kouprey", "kudu", "lapwing", "lark", "lemur", "leopard", "lion", "llama", "lobster", "locust", "loris", "louse", "lyrebird", "magpie", "mallard", "manatee", "marten", "meerkat", "mink", "monkey", "moose", "mouse", "mosquito", "mule", "narwhal", "newt", "nightingale", "octopus", "okapi", "opossum", "oryx", "ostrich", "otter", "owl", "ox", "oyster", "parrot", "partridge", "peafowl", "pelican", "penguin", "pheasant", "pig", "pigeon", "pony", "porcupine", "porpoise", "quail", "quelea", "rabbit", "raccoon", "rat", "raven", "herd", "reindeer", "rhinoceros", "ruff", "salamander", "salmon", "sandpiper", "sardine", "scorpion", "herd", "seahorse", "shark", "sheep", "shrew", "shrimp", "skunk", "snail", "snake", "spider", "squid", "squirrel", "starling", "stingray", "stinkbug", "stork", "swallow", "swan", "tapir", "tarsier", "termite", "tiger", "toad", "trout", "poultry", "turtle", "vulture", "wallaby", "walrus", "wasp", "carabeef", "weasel", "whale", "wolf", "wolverine", "wombat", "woodcock", "woodpecker", "worm", "wren", "yak", "zebra"];
        $adjective = array_rand($adjectives);
        $animal = array_rand($animals);
       return  $db_name . '-' . date($this->date_format) . '--' . $adjectives[$adjective] . '-' . $animals[$animal] . '.sql';
    }

    // Dump the database into a SQL file, return true on success
    public function dump() {
        $mysqldump_bin = Symphony::Configuration()->get('mysqldump_bin', 'db_manager');
        $db_host = Symphony::Configuration()->get('host', 'database');
        $db_port = Symphony::Configuration()->get('port', 'database');
        $db_name = Symphony::Configuration()->get('db', 'database');
        $db_user = Symphony::Configuration()->get('user', 'database');
        $db_password = Symphony::Configuration()->get('password', 'database');

        if (is_null($this->filename)) {
            //$this->filename = $db_name . '-' . date($this->date_format) . '.sql';
            $this->filename = $this->generateFilename();
        }
        else {
            return false; // don't support replacing an existing backup for now
        }

        $return_value = null;
        $output = array();
        $command = "{$mysqldump_bin} --host={$db_host} --port={$db_port} --user={$db_user} --password={$db_password} {$db_name} --result-file={$this->file()} 2>&1";
        exec($command, $output, $return_value);
        $this->setExecutedCommand($command);

        if ($return_value == 0) {
            return true;
        }

        $this->setError($return_value, $output);
        return false;
    }

    // Restore the backup from raw SQL, return true on success
    public function restore() {
        $mysql_bin = Symphony::Configuration()->get('mysql_bin', 'db_manager');
        $db_host = Symphony::Configuration()->get('host', 'database');
        $db_port = Symphony::Configuration()->get('port', 'database');
        $db_name = Symphony::Configuration()->get('db', 'database');
        $db_user = Symphony::Configuration()->get('user', 'database');
        $db_password = Symphony::Configuration()->get('password', 'database');

        $sql_file = self::directory() . pathinfo($this->file(), PATHINFO_FILENAME);
        $return_value = null;
        $output = array();
        $command = "{$mysql_bin} --host={$db_host} --port={$db_port} --user={$db_user} --password={$db_password} {$db_name} < {$sql_file} 2>&1";

        exec($command, $output, $return_value);
        $this->setExecutedCommand($command);
        unlink($sql_file);

        if ($return_value == 0) {
            return true;
        }

        $this->setError($return_value, $output);
        return false;
    }

    // Compress the backup with gzip, return true on success
    public function zip() {
        $gzip_bin = Symphony::Configuration()->get('gzip_bin', 'db_manager');
        $command = "{$gzip_bin} -9 {$this->file()} 2>&1"; // gzip automatically deletes the input file for us
        $return_value = null;
        $output = array();

        exec($command, $output, $return_value);
        $this->setExecutedCommand($command);

        if ($return_value == 0) {
            $this->filename .= '.gz';
            return true;
        }

        $this->setError($return_value, $output);
        return false;
    }

    // Uncompress the backup with gzip, return true on success
    public function unzip() {
        if (is_null($this->filename)) {
            return false;
        }

        $parts = pathinfo($this->filename);
        if ($parts['extension'] != 'gz') {
            return false;
        }

        $gunzip_bin = Symphony::Configuration()->get('gunzip_bin', 'db_manager');
        $return_value = null;
        $output = array();
        $command = "{$gunzip_bin} --keep --force {$this->file()} 2>&1";

        exec($command, $output, $return_value);
        $this->setExecutedCommand($command);

        if ($return_value == 0) {
            return true;
        }

        $this->setError($return_value, $output);
        return false;
    }

    // Set the error code and output for a failed backup
    public function setError($return_value, $output) {
        $this->failed = true;
        $this->return_value = $return_value;
        $this->command_output = $output;
    }

    // Return the error message from the last failed command
    public function errorMsg() {
        return $this->command_output[0];
    }

    // Returns whether the backup has explicitly failed
    public function hasFailed() {
        return $this->failed == true;
    }

    // Set the command that was last executed
    public function setExecutedCommand($command) {
        $this->executed_command = $command;
    }

    // Return the command that was last executed
    public function executedCommand() {
        return $this->executed_command;
    }

    // Return a date object of the backups creation time
    public function getDate() {
        $db_name = Symphony::Configuration()->get('db', 'database');
        $no_codename = preg_replace('/--[a-z]+-[a-z]+/', '', $this->filename());
        $needles = [self::directory(), $db_name . '-', '.sql.gz'];
        $date_string = str_replace($needles, '', $no_codename);
        $date = DateTime::createFromFormat($this->date_format, $date_string);
        return $date; 
    }
}

