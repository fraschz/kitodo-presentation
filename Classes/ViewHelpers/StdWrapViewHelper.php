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

namespace Kitodo\Dlf\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StdWrapViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('wrap', 'string', 'The wrap information', true);
        $this->registerArgument('data', 'array', 'Data for the content object', false);
    }

    /**
     * Wraps the given value
     *
     * @return string
     */
    public function render()
    {
        $wrap = $this->arguments['wrap'];
        $data = $this->arguments['data'] ?? [];

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $cObj = $configurationManager->getContentObject();

        $insideContent = $this->renderChildren();

        $prevData = $cObj->data;
        $cObj->data = $data;
        try {
            $result = $cObj->stdWrap($insideContent, $wrap);
        } finally {
            $cObj->data = $prevData;
        }

        return $result;
    }
}
