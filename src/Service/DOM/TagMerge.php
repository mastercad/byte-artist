<?php
/**
 * Created by PhpStorm.
 * User: mastercad
 * Date: 16.04.15
 * Time: 19:03
 */
namespace App\Service\DOM;

class TagMerge
{
    private $_sString = null;
    private $_sContent = null;
    private $_sFirstAttributes = null;
    private $_sSecondAttributes = null;

    /** @var array Container mit den Attributen, die gemerged werden können */
    private $_aAttributeMergePossible = array(
        'span',
        'class'
    );

    public function setString($sString)
    {
        $this->_sString = $sString;
        return $this;
    }

    public function getString()
    {
        return $this->_sString;
    }

    public function merge($sString)
    {
        $this->setString($sString);
        while ($this->_splitStringInEmptyTags()) {
            $aFirstAttributes = $this->_extractAttributesFromString($this->_sFirstAttributes);
            $aSecondAttributes = $this->_extractAttributesFromString($this->_sSecondAttributes);

            $this->setString(
                $this->_replaceMergedContent(
                    $this->_mergeAttributes(
                        $aFirstAttributes,
                        $aSecondAttributes
                    )
                )
            );
        };
        return $this->getString();
    }

    private function _splitStringInEmptyTags()
    {
        $bReturn = false;
        if (preg_match('/<span\s*([a-z0-9,\.;:\|\s=\-_#\'"]*)>\s*<span\s*([\sa-z0-9,\.;:\|=\-_"#\']*)>(.*?)<\/span>\s*<\/span>/si', $this->getString(), $aMatches)) {
            $this->_sFirstAttributes = $aMatches[1];
            $this->_sSecondAttributes = $aMatches[2];
            $this->_sContent = $aMatches[3];
            $bReturn = true;
        }
        return $bReturn;
    }

    private function _replaceMergedContent($sMergedContent)
    {
        $sContent = preg_replace('/<span\s*[a-z0-9,\.;:\|\s=\-_#\'"]*>\s*<span\s*[\sa-z0-9,\.;:\|=\-_"#\']*>.*?<\/span>\s*<\/span>/is', '<span ' . $sMergedContent . '>' . $this->_sContent . '</span>', $this->getString());
        return $sContent;
    }

    private function _extractAttributesFromString($sString)
    {
        $mReturn = array();
        /** @fixme hier gibts noch einen bug wenn ein attribute value keine anführungszeichen hat
         * -> invalid und mir daher gerade erstmal latte, es soll für den replacer dienen und
         * da gibts sowas nicht
         */
        if (preg_match_all('/([a-z\-]+)=(["|\'| |]*)([a-z0-9,\.;:\|\s\-_#]*?)\2/is', $sString, $aMatches)) {
            foreach ($aMatches[0] as $iKey => $aMatch) {
                $sAttribute = $aMatches[1][$iKey];
                $sValues = $aMatches[3][$iKey];
                if (false === array_key_exists($sAttribute, $mReturn)) {
                    $mReturn[$sAttribute] = '';
                }
                $mReturn[$sAttribute] .= $sValues;
            }
        }
        return $mReturn;
    }

    private function _mergeAttributes($aFirstAttributes, $aSecondAttributes)
    {
        $sAttributes = '';
        $aProcessedAttributes = array();
        foreach ($aFirstAttributes as $sAttribute => $sValues) {
            if (true === array_key_exists($sAttribute, $aSecondAttributes)) {
                if (strtoupper($sAttribute) == "STYLE") {
                    $sMergedAttributes = $this->_mergeStyleAttributes($sValues, $aSecondAttributes[$sAttribute]);
                    $aProcessedAttributes[$sAttribute] = $sAttribute;
                } elseif (strtoupper($sAttribute) == "CLASS") {
                    $sMergedAttributes = $this->_mergeClassAttributes($sValues, $aSecondAttributes[$sAttribute]);
                    $aProcessedAttributes[$sAttribute] = $sAttribute;
                } else {
                    // kein zusammenführbares attribut, daher hier das äußere nehmen!
                    $sMergedAttributes = $sValues;
                    $aProcessedAttributes[$sAttribute] = $sAttribute;
                }
            } else {
                $sMergedAttributes = $sValues;
                $aProcessedAttributes[$sAttribute] = $sAttribute;
            }
            $sAttributes .= $sAttribute . '="' . $sMergedAttributes . '" ';
        }

        foreach ($aProcessedAttributes as $sAttribute) {
            unset($aFirstAttributes[$sAttribute]);
            unset($aSecondAttributes[$sAttribute]);
        }

        foreach ($aFirstAttributes as $sAttribute => $sValues) {
            $sAttributes .= $sAttribute . '="' . $sValues . '" ';
        }

        foreach ($aSecondAttributes as $sAttribute => $sValues) {
            $sAttributes .= $sAttribute . '="' . $sValues . '" ';
        }
        return trim($sAttributes);
    }

    private function _mergeStyleAttributes($sValuesFirst, $sValuesSecond)
    {
        $sValuesFirst = trim($sValuesFirst);
        $sValuesSecond = trim($sValuesSecond);

        if (';' == substr($sValuesFirst, -1)) {
            $sValuesFirst = substr($sValuesFirst, 0, -1);
        }
        if (';' != substr($sValuesSecond, -1)) {
            $sValuesSecond .= ';';
        }
        return $sValuesFirst . '; ' . $sValuesSecond;
    }

    private function _mergeClassAttributes($sValuesFirst, $sValuesSecond)
    {
        $sValuesFirst = trim($sValuesFirst);
        $sValuesSecond = trim($sValuesSecond);

        return $sValuesFirst . ' ' . $sValuesSecond;
    }
}
