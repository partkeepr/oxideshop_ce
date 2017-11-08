<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop CE
 */
namespace OxidEsales\EshopCommunity\Tests\Integration\Seo;

/**
 * Class GetManufacturerSeoTest
 *
 * @package OxidEsales\EshopCommunity\Tests\Integration\Seo
 */
class GetManufacturerSeoTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /** @var string Original theme */
    private $origTheme;
    /**
     * @var string
     */
    private $seoUrl = '';
    /**
     * @var string
     */
    private $categoryOxid = '';
    /**
     * Sets up test
     */
    protected function setUp()
    {
        parent::setUp();
        $this->origTheme = $this->getConfig()->getConfigParam('sTheme');
        $query = "UPDATE `oxconfig` SET `OXVARVALUE` = encode('azure', 'fq45QS09_fqyx09239QQ') WHERE `OXVARNAME` = 'sTheme'";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
        $this->getConfig()->saveShopConfVar('bool', 'blEnableSeoCache', false);
        $this->cleanRegistry();
        $this->cleanSeoTable();
        $facts = new \OxidEsales\Facts\Facts;
        $this->seoUrl = ('EE' == $facts->getEdition()) ? 'Party/Bar-Equipment/' : 'Geschenke/';
        $this->categoryOxid = ('EE' == $facts->getEdition()) ? '30e44ab8593023055.23928895' : '8a142c3e4143562a5.46426637';
    }
    /**
     * Tear down test.
     */
    protected function tearDown()
    {
        $this->cleanRegistry();
        $this->cleanSeoTable();
        $_GET = [];
        parent::tearDown();
    }
    /**
     * Test SeoEncoderManufacturer::getManufacturerPageUrl().
     * Test saving to database.
     */
    public function testGetManufacturerPageUrl()
    {
        $facts = new \OxidEsales\Facts\Facts;
        if ('EE' != $facts->getEdition()) {
            $this->markTestSkipped('missing testdata');
        }
        $languageId = 1; //en
        $shopUrl = $this->getConfig()->getCurrentShopUrl();
        $manufacturerOxid = '2536d76675ebe5cb777411914a2fc8fb';
        $seoUrl = 'en/By-manufacturer/Manufacturer-2/';
        $manufacturer = oxNew(\OxidEsales\Eshop\Application\Model\Manufacturer::class);
        $manufacturer->load($manufacturerOxid);
        $seoEncoderManufacturer = oxNew(\OxidEsales\Eshop\Application\Model\SeoEncoderManufacturer::class);
        $result = $seoEncoderManufacturer->getManufacturerPageUrl($manufacturer, 2, $languageId);
        $this->assertEquals( $shopUrl . $seoUrl . '?pgNr=2', $result);
        //seo url is now stored in database and should not be saved again.
        //Test fails, because oxseo.oxtype 'oxmanufacturers' is not in the enum list for oxseo.oxtype.
        //Database entries are stored with truncated oxseo.oxtype (empty), so shop will not find them again
        //and tries again and again to store that seo page.
        $seoEncoderManufacturer = $this->getMock(\OxidEsales\Eshop\Application\Model\SeoEncoderManufacturer::class, ['_saveToDb', 'getManufacturerUri']);
        $seoEncoderManufacturer->expects($this->any())->method('getManufacturerUri')->will($this->returnValue($seoUrl));
        $seoEncoderManufacturer->expects($this->never())->method('_saveToDb');
        $seoEncoderManufacturer->getManufacturerPageUrl($manufacturer, 0, $languageId);
        $seoEncoderManufacturer->getManufacturerPageUrl($manufacturer, 1, $languageId);
        $seoEncoderManufacturer->getManufacturerPageUrl($manufacturer, 2, $languageId);
    }
    /**
     * Clean oxseo for testing.
     */
    private function cleanSeoTable()
    {
        $query = "DELETE FROM oxseo WHERE oxtype in ('oxcategory', 'oxarticle')";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
    }
    /**
     * Ensure that whatever mocks were added are removed from Registry.
     */
    private function cleanRegistry()
    {
        $seoEncoder = oxNew(\OxidEsales\Eshop\Core\SeoEncoder::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\SeoEncoder::class, $seoEncoder);
        $seoDecoder = oxNew(\OxidEsales\Eshop\Core\SeoDecoder::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\SeoDecoder::class, $seoDecoder);
        $utils = oxNew(\OxidEsales\Eshop\Core\Utils::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Utils::class, $utils);
        $request = oxNew(\OxidEsales\Eshop\Core\Request::class);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Request::class, $request);
    }
}