<?php

namespace App\Service;

use Doctrine\ORM\EntityRepository;

class Seo
{
    private $ref_db_table = null;
    private $str_table_field_name = null;
    private $str_link_name = null;
    private $str_clean_link_name = null;
    private $str_seo_name = null;
    private $str_table_field_id_name = null;
    private $i_max_count = null;
    private $i_table_field_id = null;

    public function __construct($a_params = null)
    {
        $this->i_max_count = 0;

        if (isset($a_params) &&
                is_array($a_params)) {
            if (isset($a_params['ref_db_table'])) {
                $this->ref_db_table = $a_params['ref_db_table'];
            }
            if (isset($a_params['str_table_field_name'])) {
                $this->str_table_field_name = $a_params['str_table_field_name'];
            }
            if (isset($a_params['str_link_name'])) {
                $this->str_link_name = $a_params['str_link_name'];
            }
            if (isset($a_params['str_table_field_id_name'])) {
                $this->str_table_field_id_name = $a_params['str_table_field_id_name'];
            }
            if (isset($a_params['i_table_field_id'])) {
                $this->i_table_field_id = $a_params['i_table_field_id'];
            }
        }
    }

    /**
     * @return false|null|string
     */
    public function replaceBadSigns($str_text = null)
    {
        if (!$str_text) {
            $str_text = $this->getLinkName();
        }
        if (!$str_text) {
            return false;
        }

        $str_text = strtolower($str_text);

        $a_search = [
            '/ä/',
            '/ü/',
            '/ö/',
            '/ß/',
            '/Ü/',
            '/Ä/',
            '/Ö/',
        ];

        $a_replaces = [
            'ae',
            'ue',
            'oe',
            'ss',
            'ue',
            'ae',
            'oe',
        ];

        $str_text = preg_replace($a_search, $a_replaces, $str_text);
        $str_text = preg_replace('/[^A-Za-z0-9]/i', '-', $str_text);
        $str_text = preg_replace('/\-{2,}+/', '-', $str_text);
        $str_text = preg_replace('/^\-/', '', $str_text);
        $str_text = preg_replace('/\-$/', '', $str_text);

        return $str_text;
    }

    /**
     * @return static
     */
    public function createValidLinkName($str_link): self
    {
        $this->setCleanLinkName($this->replaceBadSigns());

        return $this;
    }

    /**
     * @return static
     */
    public function createSeoLink($str_link_name = null, &$ref_obj_db = null, $str_table_field_name = null): self
    {
        if ($str_link_name) {
            $this->setLinkName($str_link_name);
        }
        if ($ref_obj_db) {
            $this->setDbTable($ref_obj_db);
        }
        if ($str_table_field_name) {
            $this->setTableFieldName($str_table_field_name);
        }
        $this->setCleanLinkName($this->replaceBadSigns($str_link_name));

        $this->createUniqueDbEntry();

        return $this;
    }

    /**
     * function zum erstellen eines eindeutigen seo link namens an hand eines
     * übergebenen string und der datenbank.
     *
     * @return false|static
     */
    public function createUniqueDbEntry($linkName = null, EntityRepository $entityRepository = null, $columnName = '')
    {
        if (!$linkName) {
            $linkName = $this->getCleanLinkName();
        }
        if (!$linkName) {
            $linkName = $this->createValidLinkName($this->getLinkName());
        }
        if (!$entityRepository) {
            $entityRepository = $this->getDbTable();
        }
        if (!$columnName) {
            $columnName = $this->getTableFieldName();
        }

        if (!strlen(trim($linkName))) {
            // echo "Habe nicht alle nötigen Parameter! Breche ab!<br />";
            return false;
        }

        $str_base_seo_name = $this->getCleanLinkName();
        $str_seo_name = $str_base_seo_name;

        /** @var EntityRepository $entityRepository */
        if ($entityRepository instanceof EntityRepository
            && $columnName
        ) {
            $i_max_count = 100;
            $i_count = 0;

            while ($i_count < $i_max_count) {
                $str_seo_name = $str_base_seo_name;

                if ($i_count > 0) {
                    $str_seo_name .= '-'.$i_count;
                }

                $row = $entityRepository->findOneBy([$columnName => $str_seo_name]);

                if (!$row
                    || (is_array($row)
                        && $row[$this->getTableFieldIdName()] == $this->getTableFieldId()
                    )
                ) {
                    break;
                } elseif (is_array($row)
                    && $row[$this->getTableFieldIdName()] == $this->getTableFieldId()
                ) {
                    break;
                }
                ++$i_count;
            }
            if ($i_count >= $i_max_count) {
                $str_seo_name = false;
            }
        }
        $this->setSeoName($str_seo_name);

        return $this;
    }

    /**
     * @return static
     */
    public function setDbTable(&$ref_db_table): self
    {
        $this->ref_db_table = $ref_db_table;

        return $this;
    }

    public function getDbTable()
    {
        return $this->ref_db_table;
    }

    /**
     * @return static
     */
    public function setTableFieldName($str_table_field_name): self
    {
        $this->str_table_field_name = trim($str_table_field_name);

        return $this;
    }

    public function getTableFieldName()
    {
        return $this->str_table_field_name;
    }

    /**
     * @return static
     */
    public function setTableFieldId($i_table_field_id): self
    {
        $this->i_table_field_id = $i_table_field_id;

        return $this;
    }

    public function getTableFieldId()
    {
        return $this->i_table_field_id;
    }

    /**
     * @return static
     */
    public function setTableFieldIdName($str_table_field_id_name): self
    {
        $this->str_table_field_id_name = trim($str_table_field_id_name);

        return $this;
    }

    public function getTableFieldIdName()
    {
        return $this->str_table_field_id_name;
    }

    /**
     * @return static
     */
    public function setLinkName($str_link_name): self
    {
        $this->str_link_name = strtolower(trim($str_link_name));

        return $this;
    }

    public function getLinkName()
    {
        return $this->str_link_name;
    }

    /**
     * @return static
     */
    public function setCleanLinkName($str_clean_link_name): self
    {
        $this->str_clean_link_name = trim($str_clean_link_name);

        return $this;
    }

    public function getCleanLinkName()
    {
        return $this->str_clean_link_name;
    }

    /**
     * @return static
     */
    public function setSeoName($str_seo_name): self
    {
        $this->str_seo_name = trim($str_seo_name);

        return $this;
    }

    public function getSeoName()
    {
        return $this->str_seo_name;
    }

    /**
     * @return static
     */
    public function setMaxCount($i_max_count): self
    {
        $this->i_max_count = $i_max_count;

        return $this;
    }

    public function getMaxCount()
    {
        return $this->i_max_count;
    }
}
