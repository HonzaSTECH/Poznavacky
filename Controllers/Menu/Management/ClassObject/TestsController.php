<?php
namespace Poznavacky\Controllers\Menu\Management\ClassObject;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\DatabaseItems\Group;

/** 
 * Kontroler starající se o výpis stránky pro správu poznávaček správcům třídy, do které patří
 * @author Jan Štěch
 */
class TestsController extends SynchronousController
{
    private array $argumentsToPass = array();

    /**
     * Metoda nastavující hlavičku stránky, data pro pohled a pohled
     * @param array $parameters Pole parametrů pro zpracování kontrolerem, zde může být na první pozici URL název kotnroleru, kterému se má předat řízení
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        self::$data['navigationBar'] = array(
            0 => array(
                'text' => self::$pageHeader['title'],
                'link' => 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests'
            )
        );

        self::$pageHeader['title'] = 'Správa poznávaček';
        self::$pageHeader['description'] = 'Nástroj pro správce tříd umožnňující snadnou správu poznávaček';
        self::$pageHeader['keywords'] = '';
        self::$pageHeader['cssFiles'] = array('css/css.css');
        self::$pageHeader['jsFiles'] = array('js/generic.js', 'js/ajaxMediator.js','js/tests.js');
        self::$pageHeader['bodyId'] = 'tests';

        self::$data['baseUrl'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage/tests';
        self::$data['groups'] = $_SESSION['selection']['class']->getGroups();
        self::$data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getUrl().'/manage';
    }
}

