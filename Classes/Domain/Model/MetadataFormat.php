<?php

/**
 * (c) Kitodo. Key to digital objects e.V. <contact@kitodo.org>
 *
 * This file is part of the Kitodo and TYPO3 projects.
 *
 * @license GNU General Public License version 3 or later.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Kitodo\Dlf\Domain\Model;

/**
 * This specifies a way how a metadatum (``tx_dlf_metadata``) may be encoded in a specific data format (``tx_dlf_format``).
 *
 * For instance, the title of a document may be obtained from either the MODS
 * title field, or from the TEIHDR caption. This is modeled as two ``tx_dlf_metadaformat``
 * that refer to the same ``tx_dlf_metadata`` but different ``tx_dlf_format``.
 *
 * This contains the xpath expressions on the model 'Metadata'.
 *
 * @package TYPO3
 * @subpackage dlf
 * @access public
 */
class MetadataFormat extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * UID of the ``tx_dlf_metadata`` that is encoded by this metadata entry.
     *
     * @var int
     */
    protected $parentId;

    /**
     * UID of the ``tx_dlf_format`` in which this metadata entry is encoded.
     *
     * @var int
     */
    protected $encoded;

    /**
     * XPath/JSONPath expression to extract the metadatum (relative to the data format root).
     *
     * @var string
     */
    protected $xpath;

    /**
     * XPath/JSONPath expression to extract sorting variant (suffixed ``_sorting``) of the metadatum.
     *
     * @var string
     */
    protected $xpathSorting;

    /**
     * Whether or not the field is mandatory. Not used at the moment (originally planned to be used in METS validator).
     *
     * @var int
     */
    protected $mandatory;

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getEncoded(): int
    {
        return $this->encoded;
    }

    /**
     * @param int $encoded
     */
    public function setEncoded(int $encoded): void
    {
        $this->encoded = $encoded;
    }

    /**
     * @return string
     */
    public function getXpath(): string
    {
        return $this->xpath;
    }

    /**
     * @param string $xpath
     */
    public function setXpath(string $xpath): void
    {
        $this->xpath = $xpath;
    }

    /**
     * @return string
     */
    public function getXpathSorting(): string
    {
        return $this->xpathSorting;
    }

    /**
     * @param string $xpathSorting
     */
    public function setXpathSorting(string $xpathSorting): void
    {
        $this->xpathSorting = $xpathSorting;
    }

    /**
     * @return int
     */
    public function getMandatory(): int
    {
        return $this->mandatory;
    }

    /**
     * @param int $mandatory
     */
    public function setMandatory(int $mandatory): void
    {
        $this->mandatory = $mandatory;
    }

}
