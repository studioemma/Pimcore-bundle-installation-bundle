# Pimcore BundleInstallationBundle

This Bundle adds helper methods to allow easy installation of Pimcore Bundles.

## Included helper methods

* Installation of Class Definitions
* Installation of Field Collections
* Installation of Object Bricks
* Create (recursive) Data Object Folder and Website setting
* Create (recursive) Asset Folder and Website setting
* Create (recursive) Document Folder and Website setting
* Create Website setting

## Installation

stable:

~~~
composer require studioemma/Pimcore-bundle-installation-bundle
~~~

unstable:

~~~
composer require studioemma/Pimcore-bundle-installation-bundle:dev-master
~~~

## Usage

Add the trait to your Installer class of the bundle

~~~
use BundleInstallationTrait;
~~~

Use the helper methods (ex.)

~~~
$this->installClassDefinition('Blog');
$this->installFieldCollection('BlogComment');
$this->installObjectBrick('BlogPostDetails');

$this->createObjectFolderAndWebsiteSetting('/blog','object_folder_blogs');
$this->createDocumentFolderAndWebsiteSetting('/blog','document_folder_posts');
$this->createAssetFolderAndWebsiteSetting('/blog', 'asset_folder_blog');
~~~


To be able to install Class Definitions, FieldCollections and ObjectBricks,
an export of the item needs to be added to your bundles "Resources/data" folder.
It needs to be the same name as provided in the install* method.