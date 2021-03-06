<?php
namespace Poznavacky\Models\DatabaseItems;

use PHPMailer\PHPMailer\Exception;
use Poznavacky\Models\Emails\EmailComposer;
use Poznavacky\Models\Emails\EmailSender;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\undefined;
use \DateTime;

/**
 * Třída reprezenzující žádost o změnu jména uživatele
 * @author Jan Štech
 */
class ClassNameChangeRequest extends NameChangeRequest
{
    public const TABLE_NAME = 'zadosti_jmena_tridy';
    
    public const COLUMN_DICTIONARY = array(
        'id' => 'zadosti_jmena_tridy_id',
        'subject' => 'tridy_id',
        'newName' => 'nove',
        'requestedAt' => 'cas',
        'newUrl' => 'nove_url'
    );
    
    protected const NON_PRIMITIVE_PROPERTIES = array(
        'subject' => ClassObject::class
    );
    
    protected const CAN_BE_CREATED = true;
    protected const CAN_BE_UPDATED = true;
    
    protected $newUrl;
    
    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param ClassObject|undefined|null $subject Instance třídy ClassObject reprezentující třídu, které se žádost týká
     * @param string|undefined|null $newName Požadované nové jméno třídy
     * @param DateTime|undefined|null $requestedAt Čas, ve kterém byla žádost podána
     * @param string|undefined|null $newUrl URL reprezentace požadovaného názvu třídy
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($subject = null, $newName = null, $requestedAt = null, $newUrl = null): void
    {
        parent::initialize($subject, $newName, $requestedAt);
        
        if ($newUrl === null) {
            $newUrl = $this->newUrl;
        }
        
        $this->newUrl = $newUrl;
    }
    
    /**
     * Metoda navracející aktuální jméno třídy
     * @return string Stávající jméno třídy
     * {@inheritDoc}
     * @see NameChangeRequest::getOldName()
     */
    public function getOldName(): string
    {
        return $this->subject->getName();
    }
    
    /**
     * Metoda navracející e-mail správce třídy, které se tato žádost týká
     * @return string E-mailová adresa správce třídy
     * {@inheritDoc}
     * @see NameChangeRequest::getRequestersEmail()
     */
    public function getRequestersEmail(): string
    {
        return $this->subject->getAdmin()[User::COLUMN_DICTIONARY['email']];
    }
    
    /**
     * Metoda schvalující tuto žádost
     * Název třídy i její URL je změněno a její správce obdrží e-mail (pokud jej zadal)
     * @return TRUE, pokud se vše povedlo, FALSE, pokud se nepodařilo odeslat e-mail
     * @throws DatabaseException
     */
    public function approve(): bool
    {
        $this->loadIfNotLoaded($this->newName);
        $this->loadIfNotLoaded($this->subject);
        
        //Načti staré jméno před jeho přepsáním novým
        $oldUrl = Folder::generateUrl($this->subject->getName());
        
        //Změnit název
        Db::executeQuery('UPDATE '.ClassObject::TABLE_NAME.' SET '.ClassObject::COLUMN_DICTIONARY['name'].' = ?, '.
                         ClassObject::COLUMN_DICTIONARY['url'].' = ? WHERE '.ClassObject::COLUMN_DICTIONARY['id'].'= ?;',
            array($this->newName, Folder::generateUrl($this->newName), $this->subject->getId()));
        
        //Uprav adresy posledních navštívených složek
        $newUrl = Folder::generateUrl($this->newName);
        Db::executeQuery('UPDATE '.User::TABLE_NAME.' SET '.LoggedUser::COLUMN_DICTIONARY['lastMenuTableUrl'].
                         ' = REPLACE('.LoggedUser::COLUMN_DICTIONARY['lastMenuTableUrl'].', ?, ?)',
            array($oldUrl, $newUrl));
        //Odeslat e-mail
        return $this->sendApprovedEmail();
    }
    
    /**
     * Metoda odesílající správci třídy, které se tato žádost týká, e-mail o potvrzení změny jména (pokud uživatel
     * zadal svůj e-mail)
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     * {@inheritDoc}
     * @throws Exception Pokud se nepodaří e-mail odeslat
     * @see NameChangeRequest::sendApprovedEmail()
     */
    public function sendApprovedEmail(): bool
    {
        $addressee = $this->getRequestersEmail();
        if (empty($addressee)) {
            return false;
        }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_CLASS_NAME_CHANGE_APPROVED, array(
            'websiteAddress' => $_SERVER['SERVER_NAME'],
            'oldName' => $this->getOldName(),
            'newName' => $this->newName
        ));
        $subject = 'Vaše žádost o změnu názvu třídy '.$this->getOldName().' na '.$this->newName.' byla schválena';
        
        return $sender->sendMail($addressee, $subject, $composer->getMail());
    }
    
    /**
     * Metoda odesílající správci třídy, které se tato žádost týká, e-mail o jejím zamítnutí (pokud uživatel zadal svůj
     * e-mail)
     * @param string $reason Důvod k zamítnutí jména zadaný správcem
     * @return bool TRUE, pokud se e-mail podařilo odeslat, FALSE, pokud ne
     * {@inheritDoc}
     * @throws Exception Pokud se nepodaří e-mail odeslat
     * @see NameChangeRequest::sendDeclinedEmail()
     */
    public function sendDeclinedEmail(string $reason): bool
    {
        $addressee = $this->getRequestersEmail();
        if (empty($addressee)) {
            return false;
        }   //E-mail není zadán
        $composer = new EmailComposer();
        $sender = new EmailSender();
        
        $composer->composeMail(EmailComposer::EMAIL_TYPE_CLASS_NAME_CHANGE_DECLINED, array(
            'websiteAddress' => $_SERVER['SERVER_NAME'],
            'oldName' => $this->getOldName(),
            'declineReason' => $reason
        ));
        $subject = 'Vaše žádost o změnu názvu třídy '.$this->getOldName().' byla zamítnuta';
        
        return $sender->sendMail($addressee, $subject, $composer->getMail());
    }
}

