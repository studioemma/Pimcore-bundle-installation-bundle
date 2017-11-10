<?php

namespace StudioEmma\BundleInstallationBundle\Traits;


use Pimcore\HttpKernel\BundleLocator\NotFoundException;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Objectbrick;
use Pimcore\Model\WebsiteSetting;

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
     * @return ClassDefinition
     * @throws \Exception
     */
    public function installClassDefinition(string $className): ClassDefinition
    {
        $class = new ClassDefinition();
        $id = $class->getDao()->getIdByName($className);
        if ($id) {
            $class =  ClassDefinition::getById($id);
        } else {
            $class = null;
        }

        if (!$class instanceof ClassDefinition) {
            $class = new ClassDefinition();
            $class->setName($className);
            $class->save();
        }

        $json = $this->getDataFile($className, 'class');
        ClassDefinition\Service::importClassDefinitionFromJson($class, $json);

        return $class;
    }

    /**
     * Install a Data Object FieldCollection
     *
     * @param string $fieldCollectionName
     * @return Fieldcollection\Definition
     * @throws \Exception
     */
    public function installFieldCollection(string $fieldCollectionName): Fieldcollection\Definition
    {
        try {
            $fieldCollection = Fieldcollection\Definition::getByKey($fieldCollectionName);
        } catch (\Exception $e) {
            $fieldCollection = null;
        }

        if (!$fieldCollection instanceof Fieldcollection\Definition) {
            $fieldCollection = new Fieldcollection\Definition();
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
     * @return Objectbrick\Definition
     * @throws \Exception
     */
    public function installObjectBrick(string $brickName): Objectbrick\Definition
    {
        try {
            $objectBrick = Objectbrick\Definition::getByKey($brickName);
        } catch (\Exception $e) {
            $objectBrick = null;
        }

        if (!$objectBrick instanceof Objectbrick\Definition) {
            $objectBrick = new Objectbrick\Definition();
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
            throw new NotFoundException(sprintf('Datafile "%s" not found for Bundle "%s"', $path,
                $this->bundle->getName()));
        }

        return file_get_contents($path);
    }

    /**
     * Create an (recursive) Object folder, recursively, and add it to a website setting
     *
     * @param string $folderPath
     * @param string $websiteSettingName
     * @return Folder
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
     * @return WebsiteSetting
     */
    public function createWebsiteSetting(string $websiteSettingName, string $type, $subject): WebsiteSetting
    {
        $setting = new WebsiteSetting();

        // We do this to avoid error logs in the cli output when the Website setting does not exist
        try {
            $setting->getDao()->getByName($websiteSettingName);
        } catch (\Exception $e) {
            $setting = null;
        }

        if (null === $setting) {
            $setting = new WebsiteSetting();
            $setting->setName($websiteSettingName);
        }

        if (is_object($subject)) {
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