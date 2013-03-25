#!/usr/bin/env php
<?php
/**
 * TODO
 *
 * - use API token if available in config when using github API
 */

include 'functions.php';

define('ROOT', realpath(__DIR__ . '/../../'));

$default_data = [
    'type'         => 'library',
    'homepage'     => 'http://packages.zendframework.com/',
    'license'      => 'BSD-3-Clause',
    'repositories' => [
        'type' => 'composer',
        'url'  => 'http://packages.zendframework.com/',
    ],
    'support'      => [
        'email'    => 'fw-general-subscribe@lists.zend.com',
        'irc'      => 'irc://irc.freenode.net/zftalk',
        'issues'   => 'https://github.com/zendframework/zf2/issues',
        'source'   => 'https://github.com/zendframework/zf2',
    ],
];

$composers = [];

// move composer files first
script_run_command('cp ' . ROOT . '/packages/component-composer/*.zip ' . ROOT . '/public/composer/');

// create packages.json
$di = new DirectoryIterator(ROOT . '/public/composer/');
foreach ($di as $file) {
    if ($file->isDot()) {
        continue;
    }

    $zip = new ZipArchive();
    $zip->open($file->getPathname());
    $composer_index_in_root = $zip->locateName('composer.json');
    if ($composer_index_in_root !== false) {
        $fp = $zip->getStream($zip->getNameIndex($composer_index_in_root));
        $composer_content = getJsonFromStream($fp);
    } else {
        $composer_index_anywhere = $zip->locateName('composer.json', ZIPARCHIVE::FL_NODIR);
        if ($composer_index_anywhere) {
            $fp = $zip->getStream($zip->getNameIndex($composer_index_anywhere));
            $composer_content = getJsonFromStream($fp);
        } else {
            $composer_content = create_composer_json_stub($file->getBasename());
        }
    }

    $composer_content = array_merge($default_data, $composer_content);

    if (!isset($composer_content['dist'])) {
        $composer_content['dist'] = [
            'url'  => 'http://packages.zendframework.com/composer/' . $file->getFilename(),
            'type' => 'zip',
        ];
    }

    $composers[$file->getBasename()] = $composer_content;
    $zip->close();
}

$zf2_metapackage_template = [
    'name'         => 'zendframework/zendframework',
    'description'  => 'Zend Framework 2',
    'type'         => 'library',
    'license'      => 'BSD-3-Clause',
    'support'      => [
        'email'    => 'fw-general-subscribe@lists.zend.com',
        'irc'      => 'irc://irc.freenode.net/zftalk',
        'issues'   => 'https://github.com/zendframework/zf2/issues',
        'source'   => 'https://github.com/zendframework/zf2',
    ],
    'keywords'     => [
        'framework',
        'zf2',
    ],
    'autoload'     => [
        'psr-0' => [
            'Zend\\' => 'library/',
            'ZendTest\\' => 'tests/',
        ],
    ],
    'source'       => [
        'type' => 'git',
        'url'  => 'git://github.com/zendframework/zf2.git',
        'reference' => 'release-',
    ],
    'dist'     => [
        'type'      => 'zip',
        'url'       => 'http://packages.zendframework.com/releases/ZendFramework-%s/ZendFramework-%s.zip',
        'reference' => 'release-',
        'shasum'    => '',
    ],
    'require'      => [
        'php'      => '>=5.3.3',
    ],
    'require-dev'  => [
        'doctrine/common' => '>=2.1',
    ],
    'suggest'  => [
        'doctrine/common'                     => 'Doctrine\Common >=2.1 for annotation features',
        'ext-intl'                            => 'ext/intl for i18n features',
        'pecl-weakref'                        => 'Implementation of weak references for Zend\Stdlib\CallbackHandler',
        'zendframework/zendpdf'               => 'ZendPdf for creating PDF representations of barcodes',
        'zendframework/zendservice-recaptcha' => 'ZendService\ReCaptcha for rendering ReCaptchas in Zend\Captcha and/or Zend\Form',
    ],
    'replace' => [
        'zendframework/zend-authentication' => 'self.version',
        'zendframework/zend-barcode' => 'self.version',
        'zendframework/zend-cache' => 'self.version',
        'zendframework/zend-captcha' => 'self.version',
        'zendframework/zend-code' => 'self.version',
        'zendframework/zend-config' => 'self.version',
        'zendframework/zend-console' => 'self.version',
        'zendframework/zend-crypt' => 'self.version',
        'zendframework/zend-db' => 'self.version',
        'zendframework/zend-debug' => 'self.version',
        'zendframework/zend-di' => 'self.version',
        'zendframework/zend-dom' => 'self.version',
        'zendframework/zend-escaper' => 'self.version',
        'zendframework/zend-eventmanager' => 'self.version',
        'zendframework/zend-feed' => 'self.version',
        'zendframework/zend-file' => 'self.version',
        'zendframework/zend-filter' => 'self.version',
        'zendframework/zend-form' => 'self.version',
        'zendframework/zend-http' => 'self.version',
        'zendframework/zend-i18n' => 'self.version',
        'zendframework/zend-inputfilter' => 'self.version',
        'zendframework/zend-json' => 'self.version',
        'zendframework/zend-ldap' => 'self.version',
        'zendframework/zend-loader' => 'self.version',
        'zendframework/zend-log' => 'self.version',
        'zendframework/zend-mail' => 'self.version',
        'zendframework/zend-math' => 'self.version',
        'zendframework/zend-memory' => 'self.version',
        'zendframework/zend-mime' => 'self.version',
        'zendframework/zend-modulemanager' => 'self.version',
        'zendframework/zend-mvc' => 'self.version',
        'zendframework/zend-navigation' => 'self.version',
        'zendframework/zend-paginator' => 'self.version',
        'zendframework/zend-permissions-acl' => 'self.version',
        'zendframework/zend-permissions-rbac' => 'self.version',
        'zendframework/zend-progressbar' => 'self.version',
        'zendframework/zend-serializer' => 'self.version',
        'zendframework/zend-server' => 'self.version',
        'zendframework/zend-servicemanager' => 'self.version',
        'zendframework/zend-session' => 'self.version',
        'zendframework/zend-soap' => 'self.version',
        'zendframework/zend-stdlib' => 'self.version',
        'zendframework/zend-tag' => 'self.version',
        'zendframework/zend-test' => 'self.version',
        'zendframework/zend-text' => 'self.version',
        'zendframework/zend-uri' => 'self.version',
        'zendframework/zend-validator' => 'self.version',
        'zendframework/zend-version' => 'self.version',
        'zendframework/zend-view' => 'self.version',
        'zendframework/zend-xmlrpc' => 'self.version',
    ],
];

$dev_master_package = $zf2_metapackage_template;
$dev_master_package['version'] = 'dev-master';
$dev_master_package['require']['zendframework/zf2'] = 'dev-master';
$dev_master_package['dist'] = array(
    'type' => 'zip',
    'url' => 'https://github.com/zendframework/zf2/zipball/master',
);
$dev_master_package['extra'] = array(
    'branch_alias' => array(
        'dev-master' => '2.1.x-dev',
    ),
);
$dev_master_commit_ref = getLastCommitByBranch('master');
if (null === $dev_master_commit_ref) {
    echo "\n\nFAILED to retrieve last commit for master branch of zf2\n\n";
}
if (null !== $dev_master_commit_ref) {
    $dev_master_package['source']['reference'] = $dev_master_commit_ref['sha'];
    $dev_master_package['source']['time'] = $dev_master_commit_ref['time'];
}
echo "Adding dev-master to ZF2 metapackage\n";
$zf2_metapackage = ['dev-master' => $dev_master_package];

$dev_develop_package = $zf2_metapackage_template;
$dev_develop_package['version'] = 'dev-develop';
$dev_develop_package['require']['zendframework/zf2'] = 'dev-develop';
$dev_develop_package['dist'] = array(
    'type' => 'zip',
    'url' => 'https://github.com/zendframework/zf2/zipball/develop',
);
$dev_develop_package['extra'] = array(
    'branch_alias' => array(
        'dev-develop' => '2.2.x-dev',
    ),
);
$dev_develop_commit_ref = getLastCommitByBranch('develop');
if (null === $dev_develop_commit_ref) {
    echo "\n\nFAILED to retrieve last commit for develop branch of zf2\n\n";
}
if (null !== $dev_develop_commit_ref) {
    $dev_develop_package['source']['reference'] = $dev_develop_commit_ref['sha'];
    $dev_develop_package['source']['time'] = $dev_develop_commit_ref['time'];
}
echo "Adding dev-develop to ZF2 metapackage\n";
$zf2_metapackage['dev-develop'] = $dev_develop_package;

$packages = [];
foreach ($composers as $filename => $composer) {
    if (!isset($composer['name'])) {
        throw new DomainException(sprintf("Composer file '%s' contains an invalid structure; no name present\nDump:\n%s", $filename, var_export($composer, 1)));
    }
    if (!isset($packages[$composer['name']])) {
        $packages[$composer['name']] = array();
    }

    // check some needed info
    if (!isset($composer['version'])) {
        list($filename_name, $filename_version) = preg_split('#-#', pathinfo($filename)['filename'], 2);
        $composer['version'] = $filename_version;
    }
    if (!isset($composer['repositories'])) {
        $composer['repositories'] = array('type' => 'composer', 'url' => 'http://packages.zendframework.com/');
    }
    if (!isset($composer['type'])) {
        $composer['type'] = 'library';
    }

    // Ensure that the source URL does not include the version
    if (isset($composer['source']) && isset($composer['source']['url'])
        && preg_match('#/(ZendService|Component_|Zend[GPQORCATM])#', $composer['source']['url'])
    ) {
        if (preg_match('/(-' . preg_quote($composer['version']) . ')\.git$/', $composer['source']['url'], $matches)) {
            $composer['source']['url'] = str_replace($matches[1], '', $composer['source']['url']);
        }
    }

    $packages[$composer['name']][$composer['version']] = $composer;

    if (!preg_match('#^zendframework/zend-#', $composer['name'])) {
        continue;
    }

    if (!isset($zf2_metapackage[$composer['version']])) {
        $zf2_metapackage[$composer['version']] = $zf2_metapackage_template;
        $zf2_metapackage[$composer['version']]['version'] = $composer['version'];
        $zf2_metapackage[$composer['version']]['source']['reference'] .= $composer['version'];
        $zf2_metapackage[$composer['version']]['dist']['url'] = sprintf($zf2_metapackage[$composer['version']]['dist']['url'], $composer['version'], $composer['version']);
        $zf2_metapackage[$composer['version']]['dist']['reference'] .= $composer['version'];
        $zf2_release_filename = sprintf(ROOT . '/public/releases/ZendFramework-%s/ZendFramework-%s.zip', $composer['version'], $composer['version']);
        if (file_exists($zf2_release_filename)) {
            $zf2_metapackage[$composer['version']]['dist']['shasum'] = sha1_file($zf2_release_filename);
        } else {
            $zf2_release_filename = sprintf(ROOT . '/packages/plain/ZendFramework-%s.zip', $composer['version']);
            if (file_exists($zf2_release_filename)) {
                $zf2_metapackage[$composer['version']]['dist']['shasum'] = sha1_file($zf2_release_filename);
            }
        }
    }
}

// Create component source packages
foreach(getComponentPackageList() as $packageName => $component) {
    if (!isset($packages[$packageName])
        || !is_array($packages[$packageName])
    ) {
        $packages[$packageName] = array();
    }
    foreach (buildComponentSourcePackages($component) as $version => $package) {
        $packages[$packageName][$version] = $package;
    }
}


echo "\nAdding zendframework/zendframework metapackage; contains following versions:\n"
    . '    ' . implode("\n    ", array_keys($zf2_metapackage)) . "\n\n";

$packages['zendframework/zendframework']        = $zf2_metapackage;
$packages['zendframework/skeleton-application'] = getSkeletonApplicationReleases();
$packages = array('packages' => $packages);

file_put_contents(ROOT . '/public/packages.json', json_encode($packages, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

function getJsonFromStream($stream)
{
    $json = stream_get_contents($stream);
    $json = str_replace('self-version', 'self.version', $json);
    return json_decode($json, true);
}

function getGithubData($uri)
{
    $uri = 'https://api.github.com' . $uri;
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    injectApiTokenHeader($ch);

    $json = curl_exec($ch);
    curl_close($ch);
    return json_decode($json);
}

function getLastCommitByBranch($branch, $repository = 'zf2')
{
    $uri        = sprintf('/repos/zendframework/%s/commits?sha=%s&per_page=1', $repository, $branch);
    $data       = getGithubData($uri);
    if (!is_array($data)) {
        return null;
    }
    $commitData = array_shift($data);
    $time       = $commitData->commit->author->date;
    $dateTime   = new \DateTime($time);
    return array(
        'sha'  => $commitData->sha,
        'time' => $dateTime->format('Y-m-d H:i:s'),
    );
}

function getSkeletonApplicationReleases()
{
    $releases = array();
    $template = array(
        'name' => 'zendframework/skeleton-application',
        'description' => 'Skeleton Application for ZF2',
        'homepage' => 'http://framework.zend.com/',
        'license' => 'BSD-3-Clause',
        'version' => '%s',
        'keywords' => array('framework', 'zf2'),
        'source' => array(
            'type' => 'git',
            'url' => 'git://github.com/zendframework/ZendSkeletonApplication.git',
        ),
        'dist' => array(
            'type' => 'zip',
            'url' => 'https://github.com/zendframework/ZendSkeletonApplication/archive/zf/release-%s.zip',
        ),
        'require' => array(
            'php' => '>=5.3.3',
            'zendframework/zendframework' => '2.*',
        ),
    );

    // Get list of tags
    $uri     = '/repos/zendframework/ZendSkeletonApplication/tags';
    $tagData = getGithubData($uri);
    if (!is_array($tagData)) {
        echo "\n\nFAILED to retrieve tag data for ZendSkeletonApplication\n\n";
        $tagData = array();
    }
    //    apply each to template
    foreach ($tagData as $tagDatum) {
        $tag     = $tagDatum->name;
        $version = $tag;
        if (preg_match('#^zf/release-#', $version)) {
            $version = substr($tag, 11);
        }

        $definition                        = $template;
        $definition['version']             = $version;
        $definition['source']['reference'] = $tag;
        $definition['dist']['url']         = sprintf($definition['dist']['url'], $version);
        $releases[$version]                = $definition;
    }

    // Add in branch "master"
    //    get last commit sha/time by branch
    $masterMetadata = getLastCommitByBranch('master', 'ZendSkeletonApplication');
    $releases['dev-master']                        = $template;
    $releases['dev-master']['version']             = 'dev-master';
    $releases['dev-master']['dist']['url']         = 'https://github.com/zendframework/ZendSkeletonApplication/zipball/master';
    if (null === $masterMetadata) {
        echo "\n\nFAILED to retrieve last commit for master branch of ZendSkeletonApplication\n\n";
    }
    if (null !== $masterMetadata) {
        $releases['dev-master']['source']['reference'] = $masterMetadata['sha'];
        $releases['dev-master']['source']['time']      = $masterMetadata['time'];
    }
    $releases['dev-master']['extra'] = array(
        'branch-alias' => array(
            'dev-master' => '2.0.x-dev',
        ),
    );

    // Return full list
    return $releases;
}

function buildComponentSourcePackages($component)
{
    $component = 'Component_' . $component;
    $packages  = array();

    foreach (['master', 'develop'] as $branch) {
        $version = 'dev-' . $branch;
        $uri     = sprintf(
            'https://raw.github.com/zendframework/%s/%s/composer.json',
            $component,
            $branch
        );

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        injectApiTokenHeader($ch);

        $json = curl_exec($ch);
        curl_close($ch);

        $package       = json_decode($json, true);
        $sourceDetails = getLastCommitByBranch($branch, $component);

        $package['version'] = $version;
        $package['source']  = [
            'type'      => 'git',
            'url'       => sprintf('git://github.com/zendframework/%s.git', $component),
            'reference' => $sourceDetails['sha'],
            'time'      => $sourceDetails['time'],
        ];

        $package['dist'] = [
            'type' => 'zip',
            'url'  => sprintf('https://github.com/zendframework/%s/zipball/%s', $component, $branch),
        ];

        // These lines will need to change as new minor releases are made
        $package['extra'] = ['branch-alias' => [
            'dev-master'  => '2.1.x-dev',
            'dev-develop' => '2.2.x-dev',
        ]];

        $packages[$version] = $package;
    }

    return $packages;
}

// This function will likely need to change as new components are added
function getComponentPackageList()
{
    return [
        'zendframework/zend-authentication'   => 'ZendAuthentication',
        'zendframework/zend-barcode'          => 'ZendBarcode',
        'zendframework/zend-cache'            => 'ZendCache',
        'zendframework/zend-captcha'          => 'ZendCaptcha',
        'zendframework/zend-code'             => 'ZendCode',
        'zendframework/zend-config'           => 'ZendConfig',
        'zendframework/zend-console'          => 'ZendConsole',
        'zendframework/zend-crypt'            => 'ZendCrypt',
        'zendframework/zend-db'               => 'ZendDb',
        'zendframework/zend-debug'            => 'ZendDebug',
        'zendframework/zend-di'               => 'ZendDi',
        'zendframework/zend-dom'              => 'ZendDom',
        'zendframework/zend-escaper'          => 'ZendEscaper',
        'zendframework/zend-eventmanager'     => 'ZendEventManager',
        'zendframework/zend-feed'             => 'ZendFeed',
        'zendframework/zend-file'             => 'ZendFile',
        'zendframework/zend-filter'           => 'ZendFilter',
        'zendframework/zend-form'             => 'ZendForm',
        'zendframework/zend-http'             => 'ZendHttp',
        'zendframework/zend-i18n'             => 'ZendI18n',
        'zendframework/zend-inputfilter'      => 'ZendInputFilter',
        'zendframework/zend-json'             => 'ZendJson',
        'zendframework/zend-ldap'             => 'ZendLdap',
        'zendframework/zend-loader'           => 'ZendLoader',
        'zendframework/zend-log'              => 'ZendLog',
        'zendframework/zend-mail'             => 'ZendMail',
        'zendframework/zend-math'             => 'ZendMath',
        'zendframework/zend-memory'           => 'ZendMemory',
        'zendframework/zend-mime'             => 'ZendMime',
        'zendframework/zend-modulemanager'    => 'ZendModuleManager',
        'zendframework/zend-mvc'              => 'ZendMvc',
        'zendframework/zend-navigation'       => 'ZendNavigation',
        'zendframework/zend-paginator'        => 'ZendPaginator',
        'zendframework/zend-permissions-acl'  => 'ZendPermissionsAcl',
        'zendframework/zend-permissions-rbac' => 'ZendPermissionsRbac',
        'zendframework/zend-progressbar'      => 'ZendProgressBar',
        'zendframework/zend-serializer'       => 'ZendSerializer',
        'zendframework/zend-server'           => 'ZendServer',
        'zendframework/zend-servicemanager'   => 'ZendServiceManager',
        'zendframework/zend-session'          => 'ZendSession',
        'zendframework/zend-soap'             => 'ZendSoap',
        'zendframework/zend-stdlib'           => 'ZendStdlib',
        'zendframework/zend-tag'              => 'ZendTag',
        'zendframework/zend-test'             => 'ZendTest',
        'zendframework/zend-text'             => 'ZendText',
        'zendframework/zend-uri'              => 'ZendUri',
        'zendframework/zend-validator'        => 'ZendValidator',
        'zendframework/zend-version'          => 'ZendVersion',
        'zendframework/zend-view'             => 'ZendView',
        'zendframework/zend-xmlrpc'           => 'ZendXmlRpc',
    ];
}

function injectApiTokenHeader($curlHandle)
{
    $config = get_github_config();
    if (!isset($config['oauth_token'])
    ) {
        return;
    }
    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, [
        sprintf('Authorization: token %s', $config['oauth_token']),
    ]);
}
