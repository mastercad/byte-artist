<?php

/**
 * Klasse die sich um alle möglichen Fileoperationen
 * kümmern soll,
 *  - upload,
 *  - verzeichnisse listen,
 *  - verschieben
 *  - verzeichnisse erstellen
 *  - zip dateien entpacken
 *  - verzeichnisse rekursiv löschen
 *  - bilder aus einem angegebenen pfad holen
 *  - etc.
 *
 * @author mastercad
 */

namespace App\Service;

class File
{
    public const SYS_PFAD = 'sys_pfad';
    public const HTML_PFAD = 'html_pfad';
    public const FILE = 'file';

    /**
     * variable, die erlaubte dateiendungen enthält, z.b. beim upload
     * nötig um schon da zu filtern.
     *
     * @var array
     */
    protected $a_allowed_extensions = [];

    /**
     * array, die den pfad oder die pfade der herkunft der datei(en) enthält.
     *
     * @var array
     */
    protected $a_source_paths = [];

    /**
     * variable, die den pfad als ziel für die datei(en) enthält.
     *
     * @var string
     */
    protected $str_dest_path = null;

    /**
     * variable, die die zu verschiebenden dateien incl. absolutem
     * pfad enthält.
     *
     * @var array
     */
    protected $a_source_files = [];

    /**
     * variable, die die uploadet files enthält, die per form auf den
     * server geladen wurden.
     *
     * @var array
     */
    protected $a_uploadet_files = [];

    /**
     * variable, die die verschobenen und/oder entpackten dateien
     * incl absolutem pfad und eventuellem systempfad enthält.
     *
     * @example array[0]['sys_pfad'] = '/var/www/public/temp/datei1.jpg'
     *          array[0]['html_pfad'] = '/temp/datei1.jpg';
     *
     * @var array
     */
    protected $a_dest_files = [];

    /**
     * function zum setzen von a_source_path, dem / den
     * herkunftspfad(en) der dateien.
     *
     * übergeben wird ein string, der das array a_source_path
     * mit sich in form eines arrays initialisiert
     *
     * @param string $str_source_path
     */
    public function setSourcePath($str_source_path)
    {
        if ($this->checkDirExists($str_source_path)) {
            $str_source_path = $this->cleanPathName($str_source_path);

            $this->a_source_paths = [$str_source_path];
        }
    }

    /**
     * function zum hinzufügen von a_source_path, dem / den
     * herkunftspfad(en) der dateien.
     *
     * übergeben werden kann ein array oder ein string mit dem pfad,
     * die funktion überprüft automatisch, ob der pfad schon vorhanden
     * ist, im negativen falle wird der übergebene pfad angefügt
     *
     * @param mixed $a_source_path
     */
    public function addSourcePath($m_source_path)
    {
        if (is_array($m_source_path)) {
            foreach ($m_source_path as $str_path) {
                $str_path = $this->cleanPathName($str_path);

                if ($this->checkDirExists($str_path) &&
                        !in_array($str_path, $this->a_source_paths)) {
                    $this->a_source_paths[] = $str_path;
                }
            }
        } elseif (is_string($m_source_path)) {
            $str_path = $this->cleanPathName($m_source_path);
            if ($this->checkDirExists($str_path) &&
                    !in_array($str_path, $this->a_source_paths)) {
                $this->a_source_paths[] = $str_path;
            } else {
                /*
                  echo "Fehler! Pfad " . $m_source_path . " wurde nicht in das
                  Array eingefügt!<br />";
                 */
            }
        }
    }

    public function clearSourcePath()
    {
        $this->a_source_paths = [];
    }

    /**
     * funktion zum reinigen eines übergebenen pfades.
     *
     * vorerst wird nur der eventuell nicht vorhandene abschließende
     * slash angefügt
     *
     * @param string $str_path
     *
     * @return string
     */
    protected function cleanPathName($str_path)
    {
        if ('/' != substr($str_path, -1)) {
            $str_path .= '/';
        }

        return $str_path;
    }

    /**
     * funktion zum checken, ob ein verzeichnis existiert.
     *
     * @param string $str_path
     *
     * @return bool
     */
    protected function checkDirExists($str_path)
    {
        if (!strlen(trim($str_path))) {
            echo 'Habe keinen Pfad übergeben bekommen!<br />';

            return false;
        }
        if (file_exists($str_path) &&
                is_dir($str_path) &&
                is_readable($str_path)) {
            return true;
        }

        return false;
    }

    /**
     * function zum zurück geben des per setSourcePath gesetzten
     * str_source_path, dem herkunftspfad der dateien.
     *
     * @return string $str_source_path
     */
    public function getSourcePaths()
    {
        return $this->a_source_paths;
    }

    /**
     * function zum setzen des str_dest_path, dem zielpfad der dateien.
     *
     * @param string $str_dest_path
     */
    public function setDestPath($str_dest_path)
    {
        $str_dest_path = $this->cleanPathName($str_dest_path);
        $this->str_dest_path = $str_dest_path;
    }

    /**
     * function zum zurück geben des per setDestPath gesetzten
     * str_dest_path, dem zielpfad der dateien.
     *
     * @return string $str_dest_path
     */
    public function getDestPath()
    {
        return $this->str_dest_path;
    }

    /**
     * function zum setzen der erlaubten dateiendungen für die bevorstehenden
     * dateioperationen.
     *
     * es kann ein array oder ein einzelner string übergeben werden,
     * in jedem falle wird daraus ein array
     *
     * @param mixed $a_allowed_extensions
     */
    public function setAllowedExtensions($m_allowed_extensions)
    {
        if (is_array($m_allowed_extensions)) {
            foreach ($m_allowed_extensions as $str_allowed_extension) {
                $this->a_allowed_extensions[] = strtolower($str_allowed_extension);
            }
        } else {
            $this->a_allowed_extensions = [strtolower($m_allowed_extensions)];
        }
    }

    /**
     * function zum zurück geben der erlaubten dateiendungen in form eines
     * arrays, die zuvor per setAllowedExtensions gesetzt wurden.
     *
     * @return array $a_allowed_extensions
     */
    public function getAllowedExtensions()
    {
        return $this->a_allowed_extensions;
    }

    /**
     * function zum setzen der zu verarbeitenden dateien, es kann ein array
     * mit dem absoluten pfad zu den dateien oder ein array mit den namen
     * der dateien übergeben werden, wobei dann der pfad mit
     * setSourcePath gesetzte seperat gesetzt werden muss.
     *
     * es kann auch eine einzelne datei übergeben werden, die dann in
     * ein array eingesetzt wird, auch hier gilt zu beachten, das bei einem
     * bloßen dateinamen der pfad explizit gesetzt werden muss
     *
     * @param mixed $m_source_files
     */
    public function setSourceFiles($m_source_files)
    {
        if (is_array($m_source_files)) {
            $this->a_source_files = $m_source_files;
        } else {
            $this->a_source_files = [$m_source_files];
        }
    }

    /**
     * function, die das zuvor per setSourceFiles gesetzte array
     * a_source_files zurück gibt.
     *
     * @return array $a_source_files
     */
    public function getSourceFiles()
    {
        return $this->a_source_files;
    }

    public function setDestFiles($m_dest_files)
    {
        /*
         * array wird an die entsprechenden geforderten keys angepasst
         */
        if (is_array($m_dest_files)) {
            foreach ($m_dest_files as &$a_dest_file) {
                if (is_string($a_dest_file)) {
                    $temp_file = $a_dest_file;
                    $basename = basename($temp_file);
                    $a_dest_file = [];
                    $a_dest_file[self::SYS_PFAD] = $temp_file;
                    $a_dest_file[self::HTML_PFAD] = str_replace(getcwd(), '', $temp_file);
                    $a_dest_file[self::FILE] = $basename;
                }
            }
            $this->a_dest_files = $m_dest_files;
        } elseif (strlen(trim($m_dest_files))) {
            $basename = basename($m_dest_files);
            $this->a_dest_files = [
                self::SYS_PFAD => $m_dest_files,
                self::HTML_PFAD => str_replace(getcwd(), '', $m_dest_files),
                self::FILE => $basename, ];
        }
    }

    public function getDestFiles()
    {
        return $this->a_dest_files;
    }

    /**
     * function, die den inhalt von a_uploadet_files setzt, das format
     * kommt dabei aus der upload file form des html elements und wird
     * direkt so übergeben.
     *
     * @param array $a_uploadet_files
     */
    public function setUploadetFiles($a_uploadet_files)
    {
        if (is_array($a_uploadet_files)) {
            $this->a_uploadet_files = $a_uploadet_files;
        }
    }

    /**
     * function, die den inhalt von a_uploadet_files zurück gibt, der
     * zuvor mit setUploadetFiles gesetzt werden muss.
     *
     * @return array $a_uploadet_files
     */
    public function getUploadetFiles()
    {
        return $this->a_uploadet_files;
    }

    /**
     * function, die die uploadet files in ihr vorgesehenes verzeichnis
     * ($str_dest_path), unterberücksichtigung von $a_allowed_extensions,
     * welche vorher per setAllowedExtensions gesetzt wurde.
     *
     * return boolean
     */
    public function moveUploadetFiles()
    {
        if (!is_array($this->getUploadetFiles())) {
            echo 'Fehler! Es wurden noch keine hochgeladenen Files übergeben!<br />';

            return false;
        }

        $localHostName = $_SERVER['SERVER_NAME'];

        if (empty($localHostName)) {
            $localHostName = $_SERVER['HTTP_HOST'];
        }

        /*
         * wenn str_source_path gesetzt und nicht null und der pfad existiert
         */
        if (strlen(trim($this->getDestPath())) &&
                $this->checkAndCreateDir($this->getDestPath())) {
            $a_files = $this->getUploadetFiles();
            $a_moved_files = [];
            $count_moved_files = 0;

            foreach ($a_files['tmp_name'] as $key => $tmp_name) {
                if (file_exists($tmp_name) &&
                        is_file($tmp_name) &&
                        is_readable($tmp_name)) {
                    $orig_name = $a_files['name'][$key];
                    $type = $a_files['type'][$key];

                    $a_fileinformation = pathinfo($orig_name);
                    $extension = strtolower($a_fileinformation['extension']);
                    $file_name = strtolower($a_fileinformation['filename']);

                    if (!count($this->getAllowedExtensions()) ||
                            in_array($extension, $this->getAllowedExtensions())) {
                        if (move_uploaded_file($tmp_name, $this->getDestPath().$orig_name)) {
                            $count_moved_files = count($a_moved_files);

                            $a_moved_files[$count_moved_files][self::SYS_PFAD] = $this->getDestPath().$orig_name;
                            $a_moved_files[$count_moved_files][self::HTML_PFAD] = 'http://'.$localHostName.
                                str_replace(getcwd(), '', $this->getDestPath()).$orig_name;
                            $a_moved_files[$count_moved_files][self::FILE] = $orig_name;
//                              $a_moved_files[$count_moved_files] = $this->getDestPath() . $orig_name;
                        } else {
                            echo 'Fehler! Konnte Datei '.$orig_name.'/'.
                            $tmp_name.' nicht verschieben!<br />';
                        }
                    } else {
                        echo 'Fehler! '.$orig_name.' Es können nur Dateien hochgeladen 
								  werden, die sich in AllowedExtensions befinden!<br />';
                    }
                } else {
                    echo 'Fehler! '.$tmp_name.' ist nicht vorhanden, kein 
							  File oder nicht lesebar!<br />';
                }
            }
            $this->setDestFiles($a_moved_files);
        } else {
            echo 'Fehler! Konnte TEMP Dir ('.$this->getDestPath().') nicht erstellen!<br />';

            return false;
        }
    }

    /**
     * funktion zum verschieben von source_path nach dest_path.
     */
    public function verschiebeFiles()
    {
        if (!$this->getSourcePaths()) {
//              echo "Habe keinen Ursprungspfad!<br />";
            return false;
        }
        if (!$this->getDestPath()) {
//              echo "Habe keinen Zielpfad!<br />";
            return false;
        }

        $a_files = $this->holeDateienAusPfad();
        $a_moved_files = [];

        foreach ($a_files as $key => $str_file_path) {
            $basename = basename($str_file_path);
            if (copy($str_file_path, $this->getDestPath().$basename)) {
                $a_moved_files[] = $this->getDestPath().$basename;
                @unlink($str_file_path);
            } else {
                echo 'Fehler! Konnte '.$str_file_path.' nicht verschieben!<br />';
            }
        }
        $this->setDestFiles($a_moved_files);
    }

    /**
     * funktion zum kopieren von source_path nach dest_path.
     */
    public function kopiereFiles()
    {
        if (!$this->getSourcePaths()) {
//              echo "Habe keinen Ursprungspfad!<br />";
            return false;
        }
        if (!$this->getDestPath()) {
//              echo "Habe keinen Zielpfad!<br />";
            return false;
        }

        $a_files = $this->holeDateienAusPfad();
        $a_copyed_files = [];

        foreach ($a_files as $key => $str_file_path) {
            $basename = basename($str_file_path);
            if (copy($str_file_path, $this->getDestPath().$basename)) {
                $a_copyed_files[] = $this->getDestPath().$basename;
            } else {
                echo 'Fehler! Konnte '.$str_file_path.' nicht verschieben!<br />';
            }
        }
        $this->setDestFiles($a_copyed_files);
    }

    /**
     * @param string $file
     * @param string $dest_dir
     */
    public function entpackeZipArchiv($file, $dest_dir)
    {
        $zip = zip_open($file);
        $count = 0;

        if ($this->getProgressbar()) {
            $adapter = new \Zend_ProgressBar_Adapter_JsPush([
                'updateMethodName' => 'updateProgressBar',
                'finishProgressBar' => 'finishProgressBar', ]);

            $obj_progress = new \Zend_ProgressBar($adapter, $count, $this->checkeZipFile($file));
        }

        if (is_resource($zip)) {
            $tree = '';
            while (($zip_entry = zip_read($zip)) !== false) {
                $last = strrpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR);
                $dir = substr(zip_entry_name($zip_entry), 0, $last);
                $file = substr(zip_entry_name($zip_entry), strrpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR));

                if ($this->getProgressbar()) {
                    $obj_progress->update($count++, ['header' => 'entpacke Zip Datei']);
                }

                if (false !== strpos(zip_entry_name($zip_entry), DIRECTORY_SEPARATOR)) {
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true) or exit("Unable to create $dir\n");
                    }
                    if (strlen(trim($file)) > 0) {
                        $return = @file_put_contents(
                            $dest_dir.$dir.'/'.$file,
                            zip_entry_read($zip_entry, zip_entry_filesize($zip_entry))
                        );
                        if (false === $return) {
                            exit("Unable to write file $dir/$file\n");
                        }
                    }
                } else {
                    file_put_contents($dest_dir.$file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
                }
            }
        }
    }

    public function checkeZipFile($file)
    {
        $zip = zip_open($file);
        $count_files = 0;

        if (is_resource($zip)) {
            while (($zip_entry = zip_read($zip)) !== false) {
                ++$count_files;
            }
        }

        return $count_files;
    }

    public function leseCsvDatenAusDir($datei_pfad)
    {
        $a_settings = ['make_headers' => false];

        $obj_csv = new \Model_CSV($a_settings);

        $a_csv = [];

        $directory = dir($datei_pfad);

        while ($file = $directory->read()) {
            if (('CSV' == strtoupper(substr($file, -3))) ||
                    ('TEV' == strtoupper(substr($file, -3))) ||
                    ('TXT' == strtoupper(substr($file, -3)))) {
                $a_temp = $obj_csv->loadFile($datei_pfad.$file);
                $a_csv = array_merge($a_csv, $a_temp);
            }
        }
        $directory->close();

        return $a_csv;
    }

    public function leseCsvDaten($datei_pfad)
    {
        $a_settings = ['make_headers' => false];

        $obj_csv = new \Model_CSV($a_settings);

        $a_csv = [];

        $a_csv = $obj_csv->loadFile($datei_pfad);

        return $a_csv;
    }

    public function writeCsvDataFromArray($sFilePath, $aData)
    {
        if (true === is_array($aData) && 0 < count($aData)
        ) {
            $oFileHandle = fopen($sFilePath, 'w+');
            if ($oFileHandle) {
                foreach ($aData as $aLine) {
                    fputcsv($oFileHandle, $aLine, ';', '"');
                }
                fclose($oFileHandle);

                return true;
            }
        }

        return false;
    }

    /**
     * function zum rekursiven löschen von verzeichnissen incl. aller
     * unterverzeichnisse und enthaltener dateien.
     *
     * wird als level 1 übergeben, wird incl zum übergebenen ordner gelöscht
     * sonst nur bis zum ordner
     *
     * @example
     *      $this->cleanDirRek('/temp/pfad/das/verzeichnis');
     *      löscht alles unterhalb von verzeichnis
     *
     *      $this->cleanDirRek('/temp/pfad/das/verzeichnis', 1);
     *      löscht alles unterhalb von verzeichnis incl verzeichnis selbst
     *
     * @param string $dir_path
     * @param int    $level
     *
     * @return bool
     */
    public function cleanDirRek($dir_path = null, $level = 0)
    {
        if (!strlen(trim($dir_path)) &&
                strlen(trim($this->getDestPath()))) {
            $dir_path = $this->getDestPath();
        } elseif (!$dir_path) {
            echo 'Fehler! Habe keinen Pfad zum löschen!<br />';

            return false;
        }

        if ('/' != substr($dir_path, -1)) {
            $dir_path = $dir_path.'/';
        }

        if (file_exists($dir_path) &&
                is_dir($dir_path)) {
            $directory = dir($dir_path);

            while ($file = $directory->read()) {
                if (('..' != $file) && ('.' != $file)) {
                    if (file_exists($dir_path.$file) &&
                            is_file($dir_path.$file) &&
                            is_readable($dir_path.$file) &&
                            is_writable($dir_path.$file)) {
                        if (false === @unlink($dir_path.$file)) {
                            echo 'Konnte Datei '.$dir_path.$file.' nicht löschen!<br />';
                        }
                    }
                    if (is_dir($dir_path.$file) &&
                            is_readable($dir_path.$file)) {
                        $this->cleanDirRek($dir_path.$file, 1);
                    }
                }
            }
            $directory->close();

            if ($level &&
                    file_exists($dir_path) &&
                    is_dir($dir_path) &&
                    $this->dirIsChildOf($dir_path, getcwd())) {
                if (false === @rmdir($dir_path)) {
                    echo 'Konnte Verzeichnis '.$dir_path.' nicht löschen!<br />';

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * function zum checken, ob ein übergebener pfad ein unterverzeichnis
     * von parent ist, das dient unter anderem um sicher zu stellen, das
     * man sich bei dateioperationen immer unterhalb public befindet.
     *
     * @param string $dir
     * @param string $parent
     */
    public function dirIsChildOf($path, $parent_path)
    {
        if ('/' == substr($path, -1)) {
            $path = substr($path, 0, -1);
        }
        if ('/' == substr($parent_path, -1)) {
            $parent_path = substr($parent_path, 0, -1);
        }
        $last_dir = str_replace('/', '', substr(
            $path,
            strrpos($path, '/'),
            strlen($path) - strrpos($path, '/')
        ));

        if (strlen(trim($last_dir)) > 1 &&
                strlen(trim(str_replace($parent_path, '', $path))) >
                strlen(trim($last_dir))) {
            return true;
        }

        return false;
    }

    /**
     * funktion zum einfachen überprüfen, ob ein verzeichnis leer ist.
     *
     * @param string $str_path
     *
     * @return bool|null
     */
    public function dirIsEmpty($str_path)
    {
        if (!is_readable($str_path)) {
            return null;
        }

        return 2 == count(scandir($str_path));
    }

    /**
     * funktion zum holen des elternpfades eines pfades.
     *
     * @param string $str_path
     *
     * @return string
     */
    public function getParentPath($str_path)
    {
        if ('/' == substr($str_path, -1)) {
            $str_path = substr($str_path, 0, -1);
        }

        return substr($str_path, 0, strrpos($str_path, '/') + 1);
    }

    public function cleanDir($dir_path)
    {
        if (file_exists($dir_path) &&
                is_dir($dir_path)) {
            $directory = dir($dir_path);

            while ($file = $directory->read()) {
                if (('..' != $file) && ('.' != $file)) {
                    if ('/' != substr($dir_path, -1)) {
                        $dir_path .= '/';
                    }
                    @unlink($dir_path.$file);
                }
            }
        }

        return true;
    }

    public function checkAndCreateDir($dir_path)
    {
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0755, true);
        }

        if (!file_exists($dir_path) ||
                !is_dir($dir_path) ||
                !is_readable($dir_path)) {
            return false;
        }

        return true;
    }

    public function mkDirFix($path)
    {
        $path = explode('/', $path);
        $conn_id = @ftp_connect('localhost');
        $currPath = '';

        if (!$conn_id) {
            return false;
        }
        if (@ftp_login($conn_id, 'ftpuser', 'oj1NOskc')) {
            foreach ($path as $dir) {
                if (!$dir) {
                    continue;
                }
                $currPath .= '/'.trim($dir);
                if (!@ftp_chdir($conn_id, $currPath)) {
                    if (!@ftp_mkdir($conn_id, $currPath)) {
                        @ftp_close($conn_id);

                        return false;
                    }
                    @ftp_chmod($conn_id, 0777, $currPath);
                }
            }
        }
        @ftp_close($conn_id);

        return $currPath;
    }

    public function holeDateienAusPfad($str_path = null)
    {
        if (strlen(trim($str_path))) {
            $this->setSourcePath($str_path);
        }

        foreach ($this->getSourcePaths() as $key => $str_path) {
            if ($handle = opendir($str_path)) {
                $a_files = [];

                while (false !== ($file = readdir($handle))) {
                    $a_fileinformationen = pathinfo($file);
                    if ('.' != $file &&
                            '..' != $file &&
                            in_array(strtolower($a_fileinformationen['extension']), $this->getAllowedExtensions())
                    ) {
                        $a_files[] = $str_path.$file;
                    }
                }
                closedir($handle);

                return $a_files;
            }

            return false;
        }
    }

    public function holeBilderAusPfad($str_path = null)
    {
        if (strlen(trim($str_path))) {
            $this->setSourcePath($str_path);
        }
        $a_bilder = [];

        foreach ($this->getSourcePaths() as $key => $str_source_path) {
            if ($handle = opendir($str_source_path)) {
                $html_pfad = str_replace(getcwd(), '', $str_source_path);
                $str_source_path = preg_replace('/\/*$/', '', $str_source_path);
                $html_pfad = preg_replace('/\/*$/', '', $html_pfad);
                $a_bilder_roh = [];

                while (false !== ($file = readdir($handle))) {
                    if ('.' != $file &&
                            '..' != $file &&
                            (
                            strstr(strtolower($file), '.gif') ||
                            strstr(strtolower($file), '.jpg') ||
                            strstr(strtolower($file), '.jpeg') ||
                            strstr(strtolower($file), '.png')
                            )
                    ) {
                        $a_bilder_roh[] = $file;
                    }
                }
                closedir($handle);

                if (is_array($a_bilder_roh) &&
                        count($a_bilder_roh) > 0) {
                    foreach ($a_bilder_roh as $file) {
                        $count_bilder = count($a_bilder);

                        $a_bilder[$count_bilder]['sys_pfad'] = $str_source_path.'/'.$file;
                        $a_bilder[$count_bilder]['html_pfad'] = $html_pfad.'/'.$file;
                        $a_bilder[$count_bilder]['file'] = $file;

                        $sort_file[$count_bilder] = $file;
                        $sort_sys_pfad[$count_bilder] = $a_bilder[$count_bilder]['sys_pfad'];
                        $sort_html_pfad[$count_bilder] = $a_bilder[$count_bilder]['html_pfad'];
                    }
                    array_multisort($sort_file, SORT_STRING, $sort_html_pfad, SORT_STRING, $a_bilder);
                }
            }
        }
        $this->setDestFiles($a_bilder);
    }

    public function ladeOrdnerStruktur($dir)
    {
        $a_struktur = [];

        if (!file_exists($dir)) {
            return false;
        }
        if (file_exists($dir) &&
                !is_dir($dir)) {
            $a_struktur = $dir;
        } elseif (file_exists($dir) &&
                is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ('.' != $file &&
                            '..' != $file &&
                            !is_dir($dir.'/'.$file)) {
                        $a_struktur[] = $file;
                    } elseif ('.' != $file &&
                            '..' != $file &&
                            is_dir($dir.'/'.$file)) {
                        $a_struktur[$file] = $this->ladeOrdnerStruktur($dir.'/'.$file);
                    }
                }
                closedir($handle);
            }
        }

        return $a_struktur;
    }
}
