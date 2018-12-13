<?php

class Installer
{
    private $bootStrapped = false;

    private $basePath;

    /**
     * PHP Extensions and their expected state
     * (enabled, disabled) in order for this
     * app to work properly.
     *
     * @var array
     */
    private $extensions = array(
        array('name' => 'fileinfo', 'type' => 'extension', 'expected' => true),
        array('name' => 'Mbstring', 'type' => 'extension', 'expected' => true),
        array('name' => 'Tokenizer', 'type' => 'extension', 'expected' => true),
        array('name' => 'XML', 'type' => 'extension', 'expected' => true),
        array('name' => 'PDO', 'type' => 'extension', 'expected' => true),
        array('name' => 'PDO_MYSQL', 'type' => 'extension', 'expected' => true),
        array('name' => 'GD', 'type' => 'extension', 'expected' => true),
        array('name' => 'OpenSSL', 'type' => 'extension', 'expected' => true),
        array('name' => 'putenv', 'type' => 'function', 'expected' => true),
        array('name' => 'getenv', 'type' => 'function', 'expected' => true),
        array('name' => 'curl', 'type' => 'extension', 'expected' => true),
    );

    /**
     * Directories that need to be writable.
     *
     * @var array
     */
    private $dirs = [
        '/',
        '/storage',
        '/storage/app',
        '/storage/framework',
        '/storage/logs',
        '/resources/views/emails/custom',
        '/.env.example'
    ];

    /**
     * Holds the compatibility check results.
     *
     * @var array
     */
    private $compatResults = ['problem' => false];

    /**
     * Installer constructor.
     */
    public function __construct()
    {
        $this->basePath = realpath(__DIR__.'/../../../');

        $post = json_decode(file_get_contents('php://input'), true);
        $data = isset($post['data']) ? $post['data'] : [];

        if ($post && array_key_exists('handler', $post)) {
            set_error_handler(function ($severity, $message) {
                echo json_encode(array('status' => 'error', 'message' => $message));
                exit;
            });

            try {
                $this->{$post['handler']}($data);
                restore_error_handler();
            } catch (Exception $e) {
                echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
                exit;
            }
        }
    }

    /**
     * Check for any issues with the server.
     *
     * @return string
     */
    public function checkForIssues()
    {
        $this->compatResults['extensions'] = $this->checkExtensions();
        $this->compatResults['folders'] = $this->checkFolders();
        $this->compatResults['phpVersion'] = $this->checkPhpVersion();

        return json_encode($this->compatResults);
    }

    /**
     * Check if we've got required php version.
     *
     * @return integer
     */
    public function checkPhpVersion()
    {
        return version_compare(PHP_VERSION, '5.6.4');
    }

    /**
     * Check if required folders are writable.
     *
     * @return array
     */
    public function checkFolders()
    {
        $checked = [];

        foreach ($this->dirs as $dir) {
            $path = $this->basePath . $dir;

            $writable = is_writable($path);

            $checked[] = array('path' => $path, 'writable' => $writable);

            if ( ! $this->compatResults['problem']) {
                $this->compatResults['problem'] = $writable ? false : true;
            }
        }

        return $checked;
    }

    /**
     * Check for any issues with php extensions.
     *
     * @return array
     */
    private function checkExtensions()
    {
        $problem = false;

        foreach ($this->extensions as $k => &$ext) {
            if ($ext['type'] === 'function') {
                $loaded = function_exists($ext['name']);
            } else {
                $loaded = extension_loaded($ext['name']);
            }

            //make notice if any extensions status
            //doesn't match what we need
            if ($loaded !== $ext['expected']) {
                $problem = true;
            }

            $ext['actual'] = $loaded;
        }

        $this->compatResults['problem'] = $problem;

        return $this->extensions;
    }

    /**
     * Insert db credentials if needed, create schema and seed the database.
     *
     * @param  array $input
     * @return array
     */
    public function createDb($input)
    {
        if ($message = $this->validateDbCredentials($input)) {
            echo json_encode(array('status' => 'error', 'message' => $message));
            exit;
        }

        $this->insertDBCredentials($input);

        $this->generateAppKey();

        $this->bootFramework();

        //fix "index is too long" issue on MariaDB and older mysql versions
        Schema::defaultStringLength(191);

        $this->prepareDatabaseForMigration($input);

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
        Artisan::call('common:seed');

        echo json_encode(array('status' => 'success'));
        exit;
    }

    private function validateDbCredentials($input)
    {
        $credentials = array_merge([
            'host' => null,
            'database' => null,
            'username' => null,
            'password' => null,
            'prefix'   => null,
        ], $input);

        $db = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];

        try {
            $db = new PDO($db, $credentials['username'], $credentials['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Insert user supplied db credentials into .env file.
     *
     * @param  array $input
     * @return void
     */
    private function insertDBCredentials(array $input)
    {
        $content = file_get_contents($this->basePath.'/.env.example');

        foreach ($input as $key => $value) {
            if ( ! $value) $value = '';
            preg_match("/(DB_$key=)(.*?)\\n/msi", $content, $matches);
            $content = str_replace($matches[1].$matches[2], $matches[1].$value, $content);
        }

        file_put_contents($this->basePath.'/.env.example', $content);
    }

    /**
     * Prepare for migration by putting new db credentials
     * into already loaded config and .env file
     *
     * @param $input
     */
    private function prepareDatabaseForMigration($input = [])
    {
        //load our new env variables and make sure environment is
        //local for migration/seeding as otherwise it will error out
        $dotenv = new Dotenv\Dotenv($this->basePath, '.env.example');
        $dotenv->load();
        App::detectEnvironment(function () {
            return 'local';
        });

        //get default database connection in case user is not using mysql
        $default = Config::get('database.default');

        if (empty($input)) {
            $input = array(
                'host' => getenv('DB_HOST'),
                'database' => getenv('DB_DATABASE'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'prefix' => getenv('DB_PREFIX'),
            );
        }

        DB::purge($default);

        $reflectionClass = new ReflectionClass(DB::connection());
        $reflectionProperty = $reflectionClass->getProperty('config');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(DB::connection(), null);

        DB::setTablePrefix($input['prefix']);
        DB::setDatabaseName($input['database']);

        //set new database credentials into config so
        //existing database connection gets updated with them
        foreach ($input as $key => $value) {
            if ( ! $value) $value = null;
            Config::set("database.connections.$default.$key", $value);
        }

        DB::reconnect($default);
    }

    /**
     * Store admin account and basic details in db.
     *
     * @param  array $input
     * @return void
     */
    public function createAdmin($input)
    {
        $this->validateAdminCredentials($input);

        $this->bootFramework();

        $this->prepareDatabaseForMigration();

        //create admin account
        $user = new App\User();
        $user->email = $input['email'];
        $user->password = Hash::make($input['password']);
        $user->permissions = ['admin' => 1, 'superAdmin' => 1];
        $user->save();

        //login user
        Auth::login($user);

        echo json_encode(array('status' => 'success'));
        exit;
    }

    private function validateAdminCredentials($input)
    {
        if (!isset($input['username'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator username.'));
            exit;
        }
        if (!isset($input['email'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator email address.'));
            exit;
        }
        if (!isset($input['password'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Please specify the administrator password.'));
            exit;
        }
        if (!isset($input['password_confirmation'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Please confirm the administrator password.'));
            exit;
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('status' => 'error', 'message' => 'Please enter a valid emails address.'));
            exit;
        }
        if (strlen($input['password']) < 4) {
            echo json_encode(array('status' => 'error', 'message' => 'Password must be at least 4 characters length'));
            exit;
        }
        if (strlen($input['username']) < 3) {
            echo json_encode(array('status' => 'error', 'message' => 'Username must be at least 4 characters length'));
            exit;
        }
        if (strcmp($input['password'], $input['password_confirmation'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Specified password does not match the confirmed password'));
            exit;
        }
    }

    /**
     * Generate new app key and put it into .env file.
     */
    private function generateAppKey()
    {
        $content = file_get_contents($this->basePath.'/.env.example');

        //set app key while we're editing .env file
        $key = 'base64:'.base64_encode($this->randomString(32));
        $content = preg_replace("/(.*?APP_KEY=).*?(.+?)\\n/msi", '${1}' . $key . "\n", $content);

        file_put_contents($this->basePath.'/.env.example', $content);
    }

    private function randomString($length = 6) {
        $str = "";
        $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /**
     * Change app env to production and set debug to false in .env file.
     *
     * @param array $input
     */
    private function putAppInProductionEnv($input = [])
    {
        $content = file_get_contents($this->basePath.'/.env.example');

        //mark as installed
        $content = preg_replace("/(.*?INSTALLED=).*?(.+?)\\n/msi", '${1}1' . "\n", $content);

        //set env to production
        $content = preg_replace("/(.*?APP_ENV=).*?(.+?)\\n/msi", '${1}production' . "\n", $content);

        //set debug to false
        $content = preg_replace("/(.*?APP_DEBUG=).*?(.+?)\\n/msi", '${1}false' . "\n", $content);

        //set base url for env
        $url = isset($input['url']) ? $input['url'] : url('');
        $content = preg_replace("/(.*APP_URL=).*?(.+?)\\n/msi", '${1}' . rtrim($url, '/') . "\n", $content);

        file_put_contents($this->basePath.'/.env.example', $content);
    }

    private function bootFramework()
    {
        if ( ! $this->bootStrapped) {
            require $this->basePath . '/bootstrap/autoload.php';

            $app = require_once $this->basePath . '/bootstrap/app.php';

            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->bootstrap();

            $this->bootStrapped = true;
        }
    }

    public function finalizeInstallation($input)
    {
        $this->bootFramework();

        $this->putAppInProductionEnv($input);

        rename($this->basePath.'/.env.example', $this->basePath.'/.env');

        Cache::flush();

        try {
            $this->deleteInstallationFiles();
        } catch (Exception $e) {
            //
        }

        echo json_encode(array('status' => 'success', 'message' => 'success'));
        exit;
    }

    private function deleteInstallationFiles()
    {
        $dir = $this->basePath . '/public/install_files';

        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        @rmdir($dir);
    }
}