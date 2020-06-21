<?php
/** 
 * Třída uchovávající data o právě přihlášeném uživateli
 * @author Jan Štěch
 */
class LoggedUser extends User
{
    static $isLogged = false;
    protected $hash;
    protected $lastChangelog;
    protected $lastLevel;
    protected $lastFolder;
    protected $theme;
    
    /**
     *
     * @param int $id ID uživatele v databázi
     * @param string $name Přezdívka uživatele
     * @param string $hash Heš uživatelova hesla z databáze
     * @param string $email E-mailová adresa uživatele
     * @param DateTime $lastLogin Datum a čas posledního přihlášení uživatele
     * @param float $lastChangelog Poslední zobrazený changelog
     * @param int $lastLevel Poslední navštívěná úroveň složek na menu stránce
     * @param int $lastFolder Poslední navštívená složka na menu stránce v určité úrovni
     * @param int $theme Zvolený vzhled stránek
     * @param int $addedPictures Počet obrázků přidaných uživatelem
     * @param int $guessedPictures Počet obrázků uhodnutých uživatelem
     * @param int $karma Uživatelova karma
     * @param string $status Uživatelův status
     */
    public function __construct(int $id, string $name, string $hash, string $email = null, DateTime $lastLogin = null, float $lastChangelog = 0, int $lastLevel = 0, int $lastFolder = null, int $theme = 0, int $addedPictures = 0, int $guessedPictures = 0, int $karma = 0, string $status = self::STATUS_MEMBER)
    {
        parent::__construct($id, $name, $email, $lastLogin, $addedPictures, $guessedPictures, $karma, $status);
        $this->hash = $hash;
        $this->lastChangelog = $lastChangelog;
        $this->lastLevel = $lastLevel;
        $this->lastFolder = $lastFolder;
        $this->theme = $theme;
    }
    
    /**
     * Metoda ukládající do databáze nový požadavek na změnu jména od přihlášeného uživatele, pokud žádný takový požadavek neexistuje nebo aktualizující stávající požadavek
     * Data jsou předem ověřena
     * @param string $newName Požadované nové jméno
     * @throws AccessDeniedException Pokud jméno nevyhovuje podmínkám systému
     * @return boolean TRUE, pokud je žádost úspěšně vytvořena/aktualizována
     */
    public function requestNameChange(string $newName)
    {
        if (mb_strlen($newName) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NO_NAME);}
        
        //Kontrola délky jména
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newName, 4, 15, 0);
        }
        catch(RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NAME_TOO_LONG, null, $e);
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_NAME_TOO_SHORT, null, $e);
            }
        }
        
        //Kontrola znaků ve jméně
        try
        {
            $validator->checkCharacters($newName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ', 0);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        
        //Kontrola dostupnosti jména
        try
        {
            $validator->checkUniqueness($newName, 0);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_NAME_CHANGE_DUPLICATE_NAME, null, $e);
        }
        
        //Kontrola dat OK
        
        //Zkontrolovat, zda již existuje žádost o změnu jména od přihlášeného uživatele
        $applications = Db::fetchQuery('SELECT zadosti_jmena_uzivatele_id FROM zadosti_jmena_uzivatele WHERE uzivatele_id = ?', array(UserManager::getId()));
        if (!empty($applications['zadosti_jmena_uzivatele_id']))
        {
            //Přepsání existující žádosti
            Db::executeQuery('UPDATE zadosti_jmena_uzivatele SET nove = ?, cas = NOW() WHERE zadosti_jmena_uzivatele_id = ? LIMIT 1', array($newName, $applications['zadosti_jmena_uzivatele_id']));
        }
        else
        {
            //Uložení nové žádosti
            Db::executeQuery('INSERT INTO zadosti_jmena_uzivatele (uzivatele_id,nove,cas) VALUES (?,?,NOW())', array($this->id, $newName));
        }
        return true;
    }
    
    /**
     * Metoda ověřující heslo přihlášeného uživatele a v případě úspěchu měnící jeho heslo
     * Všechna data jsou předem ověřena
     * @param string $oldPassword Stávající heslo pro ověření
     * @param string $newPassword Nové heslo
     * @param string $newPasswordAgain Opsané nové heslo
     * @throws AccessDeniedException Pokud některý z údajů nesplňuje podmínky systému
     * @return boolean TRUE, pokud je heslo úspěšně změněno
     */
    public function changePassword(string $oldPassword, string $newPassword, string $newPasswordAgain)
    {
        if (mb_strlen($oldPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_OLD_PASSWORD);}
        if (mb_strlen($newPassword) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newPasswordAgain) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_NO_REPEATED_PASSWORD);}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($oldPassword))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky nového hesla
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newPassword, 6, 31, 1);
        }
        catch (RangeException $e)
        {
            if ($e->getMessage() === 'long')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_TOO_LONG, null, $e);
            }
            else if ($e->getMessage() === 'short')
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_TOO_SHORT, null, $e);
            }
        }
        
        //Kontrola znaků v novém hesle
        try
        {
            $validator->checkCharacters($newPassword, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'', 1);
        }
        catch(InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_INVALID_CHARACTERS, null, $e);
        }
        //Kontrola shodnosti hesel
        if ($newPassword !== $newPasswordAgain)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_PASSWORD_CHANGE_DIFFERENT_PASSWORDS);
        }
        
        //Kontrola dat OK
        
        //Aktualizovat heslo v databázi
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        Db::connect();
        Db::executeQuery('UPDATE uzivatele SET heslo = ? WHERE uzivatele_id = ? LIMIT 1', array($hashedPassword, UserManager::getId()));
        $this->hash = $hashedPassword;
        return true;
    }
    
    /**
     * Metoda ověřující heslo uživatele a v případě úspěchu měnící e-mailovou adresu přihlášeného uživatele v databázi
     * Data jsou předtím ověřena
     * @param string $password Heslo přihlášeného uživatele pro ověření
     * @param string $newEmail Nový e-mail
     * @throws AccessDeniedException Pokud některý z údajů nesplňuje podmínky systému
     * @return boolean TRUE, pokud je e-mail úspěšně změněn
     */
    public function changeEmail(string $password, string $newEmail)
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_NO_PASSWORD);}
        if (mb_strlen($newEmail) === 0){$newEmail = NULL;}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_WRONG_PASSWORD);
        }
        
        //Kontrola délky a unikátnosti e-mailu (pokud ho uživatel nechce odstranit)
        if ($newEmail !== NULL)
        {
            $validator = new DataValidator();
            try
            {
                $validator->checkLength($newEmail, 0, 255, 2);
                $validator->checkUniqueness($newEmail, 2);
            }
            catch (RangeException $e)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_EMAIL_TOO_LONG, null, $e);
            }
            catch (InvalidArgumentException $e)
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_EMAIL_CHANGE_DUPLICATE_EMAIL, null, $e);
            }
            
            //Kontrola platnosti e-mailu
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) && !empty($newEmail))
            {
                throw new AccessDeniedException(AccessDeniedException::REASON_REGISTER_INVALID_EMAIL);
            }
        }
        
        //Kontrola dat OK
        
        //Aktualizovat databázi
        Db::connect();
        Db::executeQuery('UPDATE uzivatele SET email = ? WHERE uzivatele_id = ? LIMIT 1', array($newEmail, UserManager::getId()));
        $this->email = $newEmail;
        return true;
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli přidaných obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementAddedPictures()
    {
        $this->addedPictures++;
        Db::connect();
        return Db::executeQuery('UPDATE uzivatele SET pridane_obrazky = (pridane_obrazky + 1) WHERE uzivatele_id = ?', array($this->id));
    }
    
    /**
     * Metoda přidávající uživateli jak v $_SESSION tak v databázi jeden bod v poli uhodnutých obrázků
     * @return boolean TRUE, pokud vše proběhne hladce
     */
    public function incrementGuessedPictures()
    {
        $this->guessedPictures++;
        Db::connect();
        return Db::executeQuery('UPDATE uzivatele SET uhodnute_obrazky = (uhodnute_obrazky + 1) WHERE uzivatele_id = ?', array($this->id));
    }
    
    /**
     * Metoda ověřující heslo přihlášeného uživatele a v případě úspěchu odstraňující jeho uživatelský účet
     * Po odstranění z databáze jsou uživatelova data vymazána i ze $_SESSION
     * @param string $password Heslo přihlášeného uživatele pro ověření
     * @throws AccessDeniedException Pokud není heslo správné, vyplněné nebo uživatel nemůže smazat svůj účet
     * @return boolean TRUE, pokud je uživatel úspěšně odstraněn z databáze a odhlášen
     */
    public function deleteAccount(string $password)
    {
        if (mb_strlen($password) === 0){throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_NO_PASSWORD);}
        
        //Kontrola hesla
        if (!AccessChecker::recheckPassword($password))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_WRONG_PASSWORD);
        }
        
        //Kontrola, zda uživatel není správcem žádné třídy
        Db::connect();
        $administratedClasses = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE spravce = ? LIMIT 1', array(UserManager::getId()));
        if ($administratedClasses['cnt'] > 0)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_ACCOUNT_DELETION_CLASS_ADMINISTRATOR);
        }
        
        //Kontrola dat OK
        
        //Odstranit uživatele z databáze
        Db::executeQuery('DELETE FROM uzivatele WHERE uzivatele_id = ?', array(UserManager::getId()));
        
        //Odhlásit uživatele
        unset($_SESSION['user']);
        return true;
    }
}