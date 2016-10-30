<?php
include ('config.php');

// On initialise le timeZone
ini_set ( 'date.timezone', 'Europe/Paris' );

// On ajoute l'autoloader (compatible winwin)
$loader = require_once join ( DIRECTORY_SEPARATOR, [ 
		dirname ( __DIR__ ),
		'vendor',
		'autoload.php' 
] );

// dans l'autoloader nous ajoutons notre répertoire applicatif
$loader->addPsr4 ( 'App\\', join ( DIRECTORY_SEPARATOR, [ 
		dirname ( __DIR__ ),
		'src' 
] ) );

// Nous instancions un objet Silex\Application
$app = new Silex\Application ();

// connexion à la base de données
$app->register ( new Silex\Provider\DoctrineServiceProvider (), array (
		'db.options' => array (
				'driver' => 'pdo_mysql',
				'dbhost' => hostname,
				'host' => hostname,
				'dbname' => database,
				'user' => username,
				'password' => password,
				'charset' => 'utf8mb4' 
		) 
) );

// utilisation des sessions
$app->register ( new Silex\Provider\SessionServiceProvider () );

// en dev, nous voulons voir les erreurs
$app ['debug'] = true;
// rajoute la méthode asset dans twig

$app->register ( new Silex\Provider\AssetServiceProvider (), array (
		'assets.named_packages' => array (
				'css' => array (
						'version' => 'css2',
						'base_path' => __DIR__ . '/../web/' 
				) 
		) 
) );
// par défaut les méthodes DELETE PUT ne sont pas prises en compte
use Symfony\Component\HttpFoundation\Request;

Request::enableHttpMethodParameterOverride ();

include 'routing.php';

use App\Provider\UseProvider;
// Ajout des régles de sécurité
$app->register ( new Silex\Provider\SecurityServiceProvider (), array (
		'security.firewalls' => array (
				'login_path' => array (
						'pattern' => '^/user/login$',
						'anonymous' => true 
				),
				'default' => array (
						'pattern' => '^/.*$',
						'anonymous' => true,
						'form' => array (
								'login_path' => '/user/login',
								'check_path' => 'login_check' 
						),
						'logout' => array (
								'logout_path' => '/logout',
								'invalidate_session' => false 
						),
						'users' => function ($app) {
							return new App\Provider\UseProvider ( $app ['db'] );
						} 
				) 
		),
		'security.access_rules' => array (
				array (
						'^/user/login$',
						'IS_AUTHENTICATED_ANONYMOUSLY' 
				),
				array (
						'^/*show$',
						'IS_AUTHENTICATED_ANONYMOUSLY' 
				),
				array (
						'^/*add*$',
						'ROLE_ADMIN' 
				),
				array (
						'^/*edit*$',
						'ROLE_ADMIN' 
				),
				array (
						'^/*delete*$',
						'ROLE_ADMIN' 
				),
				array (
						'^/.+$',
						'ROLE_USER' 
				) 
		) 
) );
// Initilisation des services
$app->boot ();
// On ajoute Twig après pour qu'il detect les rélges de secu
// utilisation de twig
$app->register ( new Silex\Provider\TwigServiceProvider (), array (
		'twig.path' => join ( DIRECTORY_SEPARATOR, [ 
				dirname ( __DIR__ ),
				'src',
				'View' 
		] ) 
) );

$app->run ();