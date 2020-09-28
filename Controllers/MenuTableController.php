<?php
/** 
 * Kontroler starající se o rozhodnutí, jaká tabulka se bude zobrazovat na menu stránce
 * Tato třída nastavuje pohled obsahující poze tlačítko pro návrat a/nebo chybovou hlášku
 * Pohled obsahující samotnou tabulku a její obsah je nastavován kontrolerem MenuTableContentController
 * @author Jan Štěch
 */
class MenuTableController extends Controller
{

    /**
     * Metoda nastavující informace pro hlavičku stránky a získávající data do tabulky
     * @see Controller::process()
     */
    public function process(array $chosenFolder)
    {
        $this->pageHeader['title'] = 'Volba poznávačky';
        $this->pageHeader['description'] = 'Zvolte si poznávačku, na kterou se chcete učit.';
        $this->pageHeader['keywords'] = 'poznávačky, biologie, příroda';
        $this->pageHeader['cssFiles'] = array('css/css.css');
        $this->pageHeader['jsFiles'] = array('js/generic.js','js/menu.js');
        $this->pageHeader['bodyId'] = 'menu';
        
        //Získání dat
        try
        {
            if (!isset($_SESSION['selection']['class']))
            {
                $this->view = 'menuClassesForms';
                $classes = TestGroupsManager::getClasses();
                $this->controllerToCall = new MenuTableContentController('menuClassesTable', $classes);
            }
            else if (!isset($_SESSION['selection']['group']))
            {
                $this->data['returnButtonLink'] = 'menu';
                $this->view = 'menuGroupsButton';
                $groups = TestGroupsManager::getGroups($_SESSION['selection']['class']);
                $this->controllerToCall = new MenuTableContentController('menuGroupsTable', $groups);
            }
            else
            {
                $this->data['returnButtonLink'] = 'menu/'.$_SESSION['selection']['class']->getName();
                $this->view = 'menuPartsButton';
                $parts = TestGroupsManager::getParts($_SESSION['selection']['group']);
                $this->controllerToCall = new MenuTableContentController('menuPartsTable', $parts);
            }
        }
        catch (AccessDeniedException $e)
        {
            if ($e->getMessage() === AccessDeniedException::REASON_USER_NOT_LOGGED_IN)
            {
                //Uživatel není přihlášen
                $this->redirect('error403');
                return;
            }
            else
            {
                //Uživatel nemá přístup do třídy nebo poznávačky
                $this->controllerToCall = new MenuTableContentController('menuTableMessage', $e->getMessage());
            }
        }
        catch (NoDataException $e)
        {
            if ($e->getMessage() === NoDataException::UNKNOWN_CLASS || $e->getMessage() === NoDataException::UNKNOWN_GROUP || $e->getMessage() === NoDataException::UNKNOWN_PART)
            {
                $this->redirect('error404');
            }
            $this->controllerToCall = new MenuTableContentController('menuTableMessage', $e->getMessage());
        }
        
        //Obsah pro tabulku a potřebný pohled je v potomkovém kontroleru nastaven --> vypsat data
        $this->controllerToCall->process(array());
    }
}