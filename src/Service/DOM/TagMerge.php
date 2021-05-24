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
    private $sString = null;
    private $sContent = null;
    private $sFirstAttributes = null;
    private $sSecondAttributes = null;

    /** @var array Container mit den Attributen, die gemerged werden können */
    private $aAttributeMergePossible = array(
        'span',
        'class'
    );

    public function setString($sString)
    {
        $this->sString = $sString;
        return $this;
    }

    public function getString()
    {
        return $this->sString;
    }

    public function merge($sString)
    {
        $this->setString($sString);
        while ($this->splitStringInEmptyTags()) {
            $aFirstAttributes = $this->extractAttributesFromString($this->sFirstAttributes);
            $aSecondAttributes = $this->extractAttributesFromString($this->sSecondAttributes);

            $this->setString(
                $this->replaceMergedContent(
                    $this->mergeAttributes($aFirstAttributes, $aSecondAttributes)
                )
            );
        };
        return $this->getString();
    }

    private function splitStringInEmptyTags()
    {
        $bReturn = false;
        $regex = '/<span\s*([a-z0-9,\.;:\|\s=\-_#\'"]*)>\s*<span\s*([\sa-z0-9,\.;:\|=\-_"#\']*)>(.*?)<\/span>\s*'.
            '<\/span>/si';
        if (preg_match($regex, $this->getString(), $aMatches)) {
            $this->sFirstAttributes = $aMatches[1];
            $this->sSecondAttributes = $aMatches[2];
            $this->sContent = $aMatches[3];
            $bReturn = true;
        }
        return $bReturn;
    }

    private function replaceMergedContent($sMergedContent)
    {
        $regex = '/<span\s*[a-z0-9,\.;:\|\s=\-_#\'"]*>\s*<span\s*[\sa-z0-9,\.;:\|=\-_"#\']*>.*?<\/span>\s*<\/span>/is';
        $sContent = preg_replace($regex, '<span '.$sMergedContent.'>'.$this->sContent.'</span>', $this->getString());
        return $sContent;
    }

    private function extractAttributesFromString($sString)
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

    private function mergeAttributes($aFirstAttributes, $aSecondAttributes)
    {
        $sAttributes = '';
        $aProcessedAttributes = array();
        foreach ($aFirstAttributes as $sAttribute => $sValues) {
            if (true === array_key_exists($sAttribute, $aSecondAttributes)) {
                if (strtoupper($sAttribute) == "STYLE") {
                    $sMergedAttributes = $this->mergeStyleAttributes($sValues, $aSecondAttributes[$sAttribute]);
                    $aProcessedAttributes[$sAttribute] = $sAttribute;
                } elseif (strtoupper($sAttribute) == "CLASS") {
                    $sMergedAttributes = $this->mergeClassAttributes($sValues, $aSecondAttributes[$sAttribute]);
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

    private function mergeStyleAttributes($sValuesFirst, $sValuesSecond)
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

    private function mergeClassAttributes($sValuesFirst, $sValuesSecond)
    {
        $sValuesFirst = trim($sValuesFirst);
        $sValuesSecond = trim($sValuesSecond);

        return $sValuesFirst . ' ' . $sValuesSecond;
    }
}
