<?php

namespace StudioEmma\BundleInstallationBundle\Traits;

use Pimcore\Model\Exception\NotFoundException;

/**
 * Trait BundleInstallationTrait
 * @package StudioEmma\BundleInstallationBundle\Traits
 */
trait BundleInstallationTrait
{
    /**
     * Install a Data Object Class Definition
     *
     * @param string $className
     * @param bool $ignoreId Ignore the class ID provided by the JSON file (default true when using this bundle)
     * @param bool $throwException Flag to throw exception instead of logging errors
     *
     * @return \Pimcore\Model\DataObject\ClassDefinition
     * @throws \Exception
     */
    public function installClassDefinition(
        string $className,
        bool $ignoreId = true,
        bool $throwException = false
    ): \Pimcore\Model\DataObject\ClassDefinition
    {
        $class = new \Pimcore\Model\DataObject\ClassDefinition();
        try {
            $id = $class->getDao()->getIdByName($className);
        } catch (NotFoundException $e) {
            $id = false;
        }

        if ($id) {
            $class =  \Pimcore\Model\DataObject\ClassDefinition::getById($id);
        } else {
            $class = null;
        }

        if (!$class instanceof \Pimcore\Model\DataObject\ClassDefinition) {
            $class = new \Pimcore\Model\DataObject\ClassDefinition();
            $class->setName($className);
            $class->save();
        }

        $json = $this->getDataFile($className, 'class');
        \Pimcore\Model\DataObject\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, $throwException, $ignoreId);

        return $class;
    }

    /**
     * Install a Data Object FieldCollection
     *
     * @param string $fieldCollectionName
     * @return \Pimcore\Model\DataObject\Fieldcollection\Definition
     * @throws \Exception
     */
    public function installFieldCollection(string $fieldCollectionName): \Pimcore\Model\DataObject\Fieldcollection\Definition
    {
        try {
            $fieldCollection = \Pimcore\Model\DataObject\Fieldcollection\Definition::getByKey($fieldCollectionName);
        } catch (\Exception $e) {
            $fieldCollection = null;
        }

        if (!$fieldCollection instanceof \Pimcore\Model\DataObject\Fieldcollection\Definition) {
            $fieldCollection = new \Pimcore\Model\DataObject\Fieldcollection\Definition();
            $fieldCollection->setKey($fieldCollectionName);
            $fieldCollection->save();
        }

        $json = $this->getDataFile($fieldCollectionName, 'fieldcollection');
        \Pimcore\Model\DataObject\ClassDefinition\Service::importFieldCollectionFromJson($fieldCollection, $json);

        return $fieldCollection;
    }

    /**
     * Install a Data Object ObjectBrick
     *
     * @param string $brickName
     * @return \Pimcore\Model\DataObject\Objectbrick\Definition
     * @throws \Exception
     */
    public function installObjectBrick(string $brickName): \Pimcore\Model\DataObject\Objectbrick\Definition
    {
        try {
            $objectBrick = \Pimcore\Model\DataObject\Objectbrick\Definition::getByKey($brickName);
        } catch (\Exception $e) {
            $objectBrick = null;
        }

        if (!$objectBrick instanceof \Pimcore\Model\DataObject\Objectbrick\Definition) {
            $objectBrick = new \Pimcore\Model\DataObject\Objectbrick\Definition();
            $objectBrick->setKey($brickName);
            $objectBrick->save();
        }

        $json = $this->getDataFile($brickName, 'objectbrick');
        \Pimcore\Model\DataObject\ClassDefinition\Service::importObjectBrickFromJson($objectBrick, $json);

        return $objectBrick;
    }

    /**
     * Receive the content of a data file in the current bundle
     *
     * @param string $name
     * @param string $type
     * @return bool|string
     * @throws \Pimcore\HttpKernel\BundleLocator\NotFoundException
     */
    protected function getDataFile(string $name, string $type): string
    {
        $path = $this->bundle->getPath()
            . '/Resources/data/'
            . $type . '_' . $name . '_export.json';

        if (!file_exists($path)) {
            throw new \Pimcore\HttpKernel\BundleLocator\NotFoundException(sprintf('Datafile "%s" not found for Bundle "%s"', $path,
                $this->bundle->getName()));
        }

        return file_get_contents($path);
    }

    /**
     * Create an (recursive) Object folder, recursively, and add it to a website setting
     *
     * @param string $folderPath
     * @param string $websiteSettingName
     * @return \Pimcore\Model\DataObject\Folder
     * @throws \Exception
     */
    public function createObjectFolderAndWebsiteSetting(string $folderPath, string $websiteSettingName): \Pimcore\Model\DataObject\Folder
    {
        $folder = \Pimcore\Model\DataObject\Service::createFolderByPath($folderPath);

        $this->createWebsiteSetting($websiteSettingName, 'object', $folder);

        return $folder;
    }

    /**
     * Create an (recursive) Asset folder, recursively, and add it to a website setting
     *
     * @param string $folderPath
     * @param string $websiteSettingName
     * @return \Pimcore\Model\Asset\Folder
     * @throws \Exception
     */
    public function createAssetFolderAndWebsiteSetting(string $folderPath, string $websiteSettingName): \Pimcore\Model\Asset\Folder
    {
        $folder = \Pimcore\Model\Asset\Service::createFolderByPath($folderPath);

        $this->createWebsiteSetting($websiteSettingName, 'asset', $folder);

        return $folder;
    }

    /**
     * Create a (recursive) Document folder, recursively, and add it to a website setting
     *
     * @param string $folderPath
     * @param string $websiteSettingName
     * @return \Pimcore\Model\Document\Folder
     * @throws \Exception
     */
    public function createDocumentFolderAndWebsiteSetting(string $folderPath, string $websiteSettingName): \Pimcore\Model\Document\Folder
    {
        $folder = \Pimcore\Model\Document\Service::createFolderByPath($folderPath);

        $this->createWebsiteSetting($websiteSettingName, 'document', $folder);

        return $folder;
    }

    /**
     * Create a Website setting
     *
     * @param string $websiteSettingName
     * @param string $type
     * @param $subject
     * @return \Pimcore\Model\WebsiteSetting
     */
    public function createWebsiteSetting(string $websiteSettingName, string $type, $subject): \Pimcore\Model\WebsiteSetting
    {
        $setting = new \Pimcore\Model\WebsiteSetting();

        // We do this to avoid error logs in the cli output when the Website setting does not exist
        try {
            $setting->getDao()->getByName($websiteSettingName);
        } catch (\Exception $e) {
            $setting = null;
        }

        if (null === $setting) {
            $setting = new \Pimcore\Model\WebsiteSetting();
            $setting->setName($websiteSettingName);
        }

        if ($subject instanceof \Pimcore\Model\Element\AbstractElement) {
            $subject = $subject->getId();
        }

        $setting->setValues(array(
            'type' => $type,
            'data' => $subject
        ));
        $setting->save();

        return $setting;
    }
}
