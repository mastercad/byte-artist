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
     *
     * @return void
     */
    public function setSourcePath($str_source_path): void
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
     * @param mixed $m_source_path
     *
     * @return void
     */
    public function addSourcePath($m_source_path): void
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

    public function clearSourcePath(): void
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
     * @return array $str_source_path
     */
    public function getSourcePaths()
    {
        return $this->a_source_paths;
    }

    /**
     * function zum setzen des str_dest_path, dem zielpfad der dateien.
     *
     * @param string $str_dest_path
     *
     * @return void
     */
    public function setDestPath($str_dest_path): void
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
     * @param mixed $m_allowed_extensions
     *
     * @return void
     */
    public function setAllowedExtensions($m_allowed_extensions): void
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
     *
     * @return void
     */
    public function setSourceFiles($m_source_files): void
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

    public function setDestFiles($m_dest_files): void
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

    public function getDestFiles(): array
    {
        return $this->a_dest_files;
    }

    /**
     * function, die den inhalt von a_uploadet_files setzt, das format
     * kommt dabei aus der upload file form des html elements und wird
     * direkt so übergeben.
     *
     * @param array $a_uploadet_files
     *
     * @return void
     */
    public function setUploadetFiles($a_uploadet_files): void
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
     *
     * @return false|null
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

    /**
     * @return true
     */
    public function cleanDir($dir_path): bool
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
}
