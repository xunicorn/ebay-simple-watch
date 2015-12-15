<?php
/**
 * @author Paweł Bizley Brzozowski
 * @version 1.5
 * @license BSD 2-Clause License
 * @see LICENSE file
 * 
 * MultiMailer is the Yii extension created to send or store emails in database
 * with the help of the amazing PHPMailer class.
 * https://github.com/bizley-code/Yii-MultiMailer
 * http://www.yiiframework.com/extension/multimailer
 * 
 * See Examples folder for configuration and usage examples.
 * 
 * MultiMailer requires Yii version 1.1.
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii
 * 
 * MultiMailer 1.5 uses PHPMailer version 5.2.10
 * https://github.com/PHPMailer/PHPMailer
 * PHPMailer is distributed under the LGPL 2.1 license.
 * 
 * Available methods:
 * mail()
 * SMTP
 * Gmail
 * POP before SMTP
 * Sendmail
 * qmail
 * database storage
 */

Yii::import('ext.MultiMailer.ProxyPHPMailer');

class MultiMailer extends CApplicationComponent
{
    /**
     * Default DB model column name of mail alternative body (plain text body).
     */
    const COLUMN_ALTBODY = 'alt';
    /**
     * Default DB model column name of mail body (html text body).
     */
    const COLUMN_BODY = 'body';
    /**
     * Default DB model column name of recipient's email address.
     */
    const COLUMN_EMAIL = 'email';
    /**
     * Default DB model column name of recipient's name.
     */
    const COLUMN_NAME = 'name';
    /**
     * Default DB model column name of mail subject.
     */
    const COLUMN_SUBJECT = 'subject';
    /**
     * Error messages
     */
    const ERR_DB_MODEL_INIT          = 'Error while initialising Db model.';
    const ERR_DB_MODEL_NOT_SET       = 'Database AR email model not set.';
    const ERR_DB_MODEL_SAVE          = 'Error while saving Db model.';
    const ERR_DB_PROPERTY_OTHERS     = 'Invalid database AR email model property.';
    const ERR_DB_PROPERTY_TYPE       = 'Database AR email model property must be of string or null type or false.';
    const ERR_EMAIL_INVALID          = 'Invalid email address.';
    const ERR_LANG_NOT_FOUND         = 'Language cannot be found.';
    const ERR_PHPMAILER_NOT_SET      = 'PHPMailer need to be set first.';
    const ERR_POP3_NOT_SET           = 'PHPMailer POP3 options not set.';
    const ERR_YII_CONTROLLER_NOT_SET = 'Yii controller is not set.';
    /**
     * Array key to store additional model properties.
     */
    const KEY_OTHERS = 'others';
    /**
     * Default log category name.
     */
    const LOG_CATEGORY = 'ext.MultiMailer';
    
    /**
     * PROTECTED PROPERTIES ====================================================
     * Don't need to be changed.
     */
    
    /**
     * @var array list of recipients' email addresses, default array().
     * CC and BCC recipients are stored here as well when using DB method.
     */
    protected $_addresses = array();
    /**
     * @var string|array body of mail, default null.
     */
    protected $_body = null;
    /**
     * @var boolean advanced HTML to text converter flag default false.
     * @see PHPMailer::msgHTML()
     */
    protected $_bodyAdvanced = false;
    /**
     * @var string baseline directory for images path, default ''.
     * @see PHPMailer::msgHTML()
     */
    protected $_bodyBasedir = '';
    /**
     * @var array DB AR model object validation errors, default array().
     * @since 1.2
     */
    protected $_dbModelErrors = array();
    /**
     * @var array default DB model columns list.
     */
    protected $_defaultColumns = array(
        self::COLUMN_EMAIL   => self::COLUMN_EMAIL,
        self::COLUMN_NAME    => self::COLUMN_NAME,
        self::COLUMN_SUBJECT => self::COLUMN_SUBJECT,
        self::COLUMN_BODY    => self::COLUMN_BODY,
        self::COLUMN_ALTBODY => self::COLUMN_ALTBODY,
    );
    /**
     * @var string error message, default null.
     */
    protected $_error = null;
    /**
     * @var boolean initialisation state, default false.
     */
    protected $_initState = false;
    /**
     * @var DB AR model object, default null.
     */
    protected $_model = null;
    /**
     * @var ProxyPHPMailer object, default null.
     */
    protected $_phpmailer = null;
    /**
     * @var string name of the view to be rendered, default null.
     */
    protected $_template = null;
    
    /**
     * PUBLIC PROPERTIES =======================================================
     * Changed from the Yii main config.
     */
    
    /**
     * @var string email content body type, default 'html'.
     * Options:
     * 'html' use HTML tags to format email's body
     * anything else sets body to plain text
     */
    public $setContentType = 'html';
    /**
     * @var string DB AR model's name, default ''.
     * To use with 'DB' method. This is the name of the AR model for database 
     * table used to store email to be sent later for example by Java worker.
     */
    public $setDbModel = '';
    /**
     * array DB AR model's properties, default array()
     * Default properties are:
     *  array(
     *      'email'   => 'email',
     *      'name'    => 'name',
     *      'subject' => 'subject',
     *      'body'    => 'body',
     *      'alt'     => 'alt',
     *  )
     * set as constants COLUMN_* as seen at the beginning of the class.
     * These will be added automatically so set anything here only if you want 
     * to change the column's name. For example in your DB there is the column 
     * 'address' instead of 'email' for storing sender's email address so add 
     * here
     *  array('email' => 'address').
     * If you want to skip any column just set its value to null or false.
     * If you want to add additional column with static value for the model do 
     * it like this:
     *  array(
     *      'others' => array(
     *          'column name' => 'column value',
     *          'column name' => 'column value',
     *      ),
     *  )
     * or if you want to add this per instance set it in MultiMailer object 
     * before calling MultiMailer::send i.e.:
     *  $mailer->setDbModelColumns['others'] = array('column name' => 'column value');
     * or you can use MultiMailer::db method.
     */
    public $setDbModelColumns = array();
    /**
     * @var boolean flag to set PHPMailer throwing external exceptions, default true.
     * @see PHPMailer::__construct()
     */
    public $setExternalExceptions = true;
    /**
     * @var string sender's email address, default ''.
     */
    public $setFromAddress = '';
    /**
     * @var string sender's name, default ''.
     */
    public $setFromName = '';
    /**
     * @var string|array string with language code (ISO 639-1 2-character) or array 
     * with the above code and path to the language file directory, with 
     * trailing separator (slash)
     * @see PHPMailer::setLanguage()
     * @since 1.4
     */
    public $setLanguage = '';
    /**
     * @var boolean flag to switch logging on, default true.
     * @see Yii::log()
     */
    public $setLogging = true;
    /**
     * @var string method for email sending, default 'MAIL'.
     * Options:
     * 'MAIL'     use PHP's mail function [http://php.net/manual/en/function.mail.php]
     * 'SMTP'     use SMTP server [http://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol]
     * 'GMAIL'    use Google SMTP server
     * 'POP3'     use POP3 before SMTP [http://en.wikipedia.org/wiki/Post_Office_Protocol]
     * 'SENDMAIL' use Sendmail [http://en.wikipedia.org/wiki/Sendmail]
     * 'QMAIL'    use qmail [http://en.wikipedia.org/wiki/Qmail]
     * 'DB'       store email in database instead of sending it immediately
     */
    public $setMethod = 'MAIL';
    /**
     * @var array options for PHPMailer, default array().
     * You can set any available options for PHPMailer here i.e.
     * array(
     *  'Host'     => 'mail.example.com',
     *  'Port'     => 25,
     *  'SMTPAuth' => true,
     * )
     * Some PHPMailer options are set by default so you don't need to set 
     * everything.
     * @see setDefaultPHPMailerOptions()
     * @see PHPMailer documentation for details
     */
    public $setOptions = array();
    /**
     * @var string selector for the PHPMailer validation pattern to use.
     * Options:
     * 'auto' Pick strictest one automatically (default);
     * 'pcre8' Use the squiloople.com pattern, requires PCRE > 8.0, PHP >= 5.3.2, 5.2.14;
     * 'pcre' Use old PCRE implementation;
     * 'php' Use PHP built-in FILTER_VALIDATE_EMAIL; same as pcre8 but does not allow 'dotless' domains;
     * 'html5' Use the pattern given by the HTML5 spec for 'email' type form input elements.
     * 'noregex' Don't use a regex: super fast, really dumb.
     * @see PHPMailer::validateAddress()
     * @since 1.5
     */
    public $setPattern = 'auto';
    /**
     * @var array options for PHPMailer POP3, default array().
     * You can set any available options for PHPMailer POP3 here i.e.
     * array(
     *  'Host'     => 'pop3.example.com',
     *  'Port'     => 110, // default value
     *  'Timeout'  => 30,  // default value
     *  'Username' => 'username',
     *  'Password' => 'password',
     *  'Debug'    => 0,  // default value
     * )
     * Options marked above as default can be skipped.
     * Remember to set options for SMTP in $setOptions as well ('SMTPAuth' is 
     * set automatically to false).
     * @see PHPMailer POP3 class documentation for details
     * @since 1.1
     */
    public $setPopOptions = array();
    /**
     * @var string reply email address, default ''.
     */
    public $setReplyAddress = '';
    /**
     * @var string reply name, default ''.
     */
    public $setReplyName = '';
    /**
     * @var boolean flag to set reply address and name same as sender's, default true.
     */
    public $setSameReply = true;
    /**
     * @var boolean flag to use Yii translation method for MultiMailer messages, default true.
     * @see Yii::t()
     * @since 1.2
     */
    public $setTranslation = true;
    
    /**
     * METHODS =================================================================
     */
    
    /**
     * Adds recipient with email address and name to emails array for 
     * optional DB storage.
     * @param string $address
     * @param string $name
     */
    protected function _addEmails($address, $name = '')
    {
        $address = trim($address);
        $name    = trim(preg_replace('/[\r\n]+/', '', $name));
        if ($this->getPhpmailer()->validateAddress($address)) {
            $this->_addresses[$address] = array('email' => $address, 'name' => $name);
        }
        else {
            $this->setMultiError(self::ERR_EMAIL_INVALID);
        }
    }
    
    /**
     * Authorises POP3.
     * Sets SMTP host.
     * @see POP3::authorise()
     * @since 1.1
     * @return boolean
     */
    protected function _authorisePOP3()
    {
        if (empty($this->setPopOptions['host'])) {
            $this->setMultiError(self::ERR_POP3_NOT_SET);
            return false;
        }
        
        $host     = $this->setPopOptions['Host'];
        $port     = !empty($this->setPopOptions['Port']) ? $this->setPopOptions['Port'] : false;
        $timeout  = !empty($this->setPopOptions['Timeout']) ? $this->setPopOptions['Timeout'] : false;
        $username = !empty($this->setPopOptions['Username']) ? $this->setPopOptions['Username'] : '';
        $password = !empty($this->setPopOptions['Password']) ? $this->setPopOptions['Password'] : '';
        $debug    = !empty($this->setPopOptions['Debug']) ? $this->setPopOptions['Debug'] : 0;
        
        $this->getPhpmailer()->SMTPAuth = false;
        
        return POP3::popBeforeSmtp($host, $port, $timeout, $username, $password, $debug);
    }
    
    /**
     * Adds BCC recipient with email address and name.
     * @see PHPMailer::addBCC()
     * @see _addEmails()
     * @param string $address BCC recipient's email
     * @param string $name optional BCC recipient's name
     * @since 1.1
     */
    protected function _bcc($address, $name = '')
    {
        $this->getPhpmailer()->addBCC($address, $name);
        $this->_addEmails($address, $name);
    }
    
    /**
     * Adds CC recipient with email address and name.
     * @see PHPMailer::addCC()
     * @see _addEmails()
     * @param string $address CC recipient's email
     * @param string $name optional CC recipient's name
     * @since 1.1
     */
    protected function _cc($address, $name = '')
    {
        $this->getPhpmailer()->addCC($address, $name);
        $this->_addEmails($address, $name);
    }
    
    /**
     * Sets DB AR model properties.
     * This method allows to set single property with name $names and value 
     * $value or array of properties (if so $value is ignored) with array keys 
     * being model properties and array values being property flags.
     * @see _dbColumn()
     * @param array|string $names single property or array of properties
     * @param string|null|boolean $value property flag or value, if true column 
     * is prepared to be saved under the default name, if null or false column 
     * is skipped, string value is additional column value
     * @since 1.2
     * @return boolean
     */
    protected function _db($names, $value = true)
    {
        if (is_array($names)) {
            foreach ($names as $name => $val) {
                $this->_dbColumn($name, $val);
            }
        }
        else {
            return $this->_dbColumn($names, $value);
        }
        
        return true;
    }
    
    /**
     * Sets DB AR model property.
     * @param string $name property name
     * @param string|null|boolean $value property flag or value, if true column is 
     * prepared to be saved under the default name, if null or false column is 
     * skipped, string value is additional column value
     * @since 1.2
     * @return boolean
     */
    protected function _dbColumn($name, $value = true)
    {
        if ($name == self::KEY_OTHERS) {
            $this->setMultiError(self::ERR_DB_PROPERTY_OTHERS);
            return false;
        }
        else {
            if (is_string($value) || is_null($value) || is_bool($value)) {
                if (array_key_exists($name, $this->setDbModelColumns)) {
                    $this->setDbModelColumns[$name] = ($value === true ? $name : $value);
                }
                else {
                    $this->setDbModelColumns[self::KEY_OTHERS][$name] = $value === true ? $name : $value;
                }
                
                return true;
            }
            else {
                $this->setMultiError(self::ERR_DB_PROPERTY_TYPE);
                return false;
            }
        }
    }
    
    /**
     * Initialises the DB method
     * Emails are stored in the database.
     * @see $setDbModel
     * Sets default model properties.
     * @see $_defaultColumns
     * @see $setDbModelColumns
     * Validates model properties.
     * This method requires $setDbModel to be set (AR model of the database 
     * table).
     * @return boolean
     */
    protected function _initDB()
    {
        if (!empty($this->setDbModel) && is_string($this->setDbModel)) {
            
            foreach ($this->_defaultColumns as $key => $value) {
                if (!isset($this->setDbModelColumns[$key])) {
                    $this->setDbModelColumns[$key] = $value;
                }
            }
            foreach ($this->setDbModelColumns as $key => $value) {
                if ($key != self::KEY_OTHERS) {
                    
                    if (!is_string($value) && !is_null($value) && $value !== false) {
                        
                        $this->setMultiError(self::ERR_DB_PROPERTY_TYPE);
                        return false;
                    }
                }
            }
        }
        else {
            $this->setMultiError(self::ERR_DB_MODEL_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialises the GMAIL method.
     * Emails are sent using Google SMTP server (Gmail).
     * This method presets $setOptions for SMTP credentials and requires
     * only Username (Gmail username) and Password (Gmail password).
     * This method requires PHPMailer to be initialised first.
     * @since 1.1
     * @return boolean
     */
    protected function _initGMAIL()
    {
        if (!is_null($this->getPhpmailer())) {
            $this->getPhpmailer()->isSMTP();
            $this->getPhpmailer()->SMTPAuth   = true;
            $this->getPhpmailer()->SMTPSecure = 'tls';
            $this->getPhpmailer()->Host       = 'smtp.gmail.com';
            $this->getPhpmailer()->Port       = 587;
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialises the MAIL method.
     * Emails are sent using mail() function.
     * http://php.net/manual/en/function.mail.php
     * This is the default method.
     * @return boolean
     */
    protected function _initMAIL()
    {
        return true;
    }
    
    /**
     * Initialises email sending method based on $setMethod.
     * @see $setMethod
     * Initialises the PHPMailer object with external exceptions flag.
     * @see PHPMailer::__construct()
     * Set PHPMailer options.
     * @see setPHPMailerOptions()
     */
    protected function _initMethod()
    {
        $this->_phpmailer = new ProxyPHPMailer($this->setExternalExceptions);
        $this->setPHPMailerOptions();
        
        switch (strtoupper($this->setMethod)) {
            case 'DB':
                return $this->_initDB();
            
            case 'SMTP':
                return $this->_initSMTP();
                
            case 'POP3':
                return $this->_initPOP3();
                
            case 'GMAIL':
                return $this->_initGMAIL();
                
            case 'SENDMAIL':
                return $this->_initSENDMAIL();
                
            case 'QMAIL':
                return $this->_initQMAIL();
            
            default:
                return $this->_initMAIL();
        }
    }
    
    /**
     * Initialises the POP before SMTP method.
     * Emails are sent using SMTP server with POP3 authentication.
     * This method requires $setPopOptions for POP3 authentication.
     * @see _authorisePOP3()
     * @see POP3::authorise()
     * This method requires PHPMailer to be initialised first.
     * @since 1.1
     * @return boolean
     */
    protected function _initPOP3()
    {
        if (!is_null($this->getPhpmailer())) {
            if ($this->_authorisePOP3()) {
                $this->getPhpmailer()->isSMTP();
            }
            else {
                return false;
            }
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialises the QMAIL method.
     * Emails are sent using qmail.
     * This method requires PHPMailer to be initialised first.
     * @since 1.1
     * @return boolean
     */
    protected function _initQMAIL()
    {
        if (!is_null($this->getPhpmailer())) {
            $this->getPhpmailer()->isQmail();
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialises the SENDMAIL method.
     * Emails are sent using Sendmail.
     * This method requires PHPMailer to be initialised first.
     * @since 1.1
     * @return boolean
     */
    protected function _initSENDMAIL()
    {
        if (!is_null($this->getPhpmailer())) {
            $this->getPhpmailer()->isSendmail();
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialises the SMTP method.
     * Emails are sent using SMTP server.
     * This method requires $setOptions for SMTP credentials.
     * Below are the default ones:
     * array(
     *  'Host'        => 'mail.example.com',
     *  'Port'        => 25,
     *  'SMTPAuth'    => true,
     *  'Username'    => 'yourname@example.com',
     *  'Password'    => 'yourpassword',
     *  'SMTPDebug'   => 0,
     *  'Debugoutput' => 'html',
     * )
     * @see PHPMailer::smtpConnect()
     * This method requires PHPMailer to be initialised first.
     * @return boolean
     */
    protected function _initSMTP()
    {
        if (!is_null($this->getPhpmailer())) {
            $this->getPhpmailer()->isSMTP();
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Processes the email body with optional template.
     * @see PHPMailer::msgHTML()
     * @see $_template
     * @see $_body
     * @see Yii::renderPartial()
     * @see Yii::renderFile()
     * When using email template make sure to set proper view name ($_template 
     * via template(), i.e. use '//' at the beginning etc.) and to set all the 
     * template variables as $_body array keys with values.
     * This method automatically sets AltBody to plain text version of html text
     * body when $setContentType is 'html'.
     * @throws Exception
     */
    protected function _processBody()
    {
        if (!is_null($this->_template) && is_array($this->_body)) {
            if (is_object(Yii::app()->controller)) {
                $body = Yii::app()->controller->renderPartial($this->_template, $this->_body, true);
            }
            elseif (is_object(Yii::app()->command)) {
                $body = Yii::app()->command->renderFile($this->_template, $this->_body, true);
            }
            else {
                throw new Exception(self::ERR_YII_CONTROLLER_NOT_SET);
            }
        }
        else {
            $body = $this->_body;
        }

        if ($this->setContentType == 'html') {
            $this->getPhpmailer()->msgHTML($body, $this->_bodyBasedir, $this->_bodyAdvanced);
        }
        else {
            $this->getPhpmailer()->Body = $body;
        }
    }
    
    /**
     * Validates and saves email in database using AR model for every recipient.
     * @see $setDbModelColumns
     * @see Yii::setAttribute()
     * @see Yii::save()
     * @throws Exception
     */
    protected function _saveModel()
    {
        foreach ($this->_addresses as $address) {
            
            try {
                $this->_model = new $this->setDbModel;

                if ($this->_model) {

                    foreach ($this->setDbModelColumns as $key => $value) {

                        if ($key != self::KEY_OTHERS) {

                            if (!is_null($value) || $value !== false) {

                                switch ($key) {
                                    case self::COLUMN_EMAIL:
                                        $emailProperty = $address['email'];
                                        break;

                                    case self::COLUMN_NAME:
                                        $emailProperty = $address['name'];
                                        break;

                                    case self::COLUMN_SUBJECT:
                                        $emailProperty = $this->getPhpmailer()->Subject;
                                        break;

                                    case self::COLUMN_BODY:
                                        $emailProperty = $this->getPhpmailer()->Body;
                                        break;

                                    case self::COLUMN_ALTBODY:
                                        $emailProperty = $this->getPhpmailer()->AltBody;
                                        break;

                                    default:
                                        $emailProperty = null;
                                }
                                if ($this->_model->hasAttribute($value)) {
                                    $this->_model->setAttribute($value, $emailProperty);
                                }
                            }
                        }
                        else {
                            foreach ($value as $otherKey => $otherValue) {
                                if ($this->_model->hasAttribute($otherKey)) {
                                    $this->_model->setAttribute($otherKey, $otherValue);
                                }
                            }
                        }
                    }

                    if (!($this->_model->save())) {
                        $this->_dbModelErrors = $this->_model->getErrors();
                        throw new Exception(self::ERR_DB_MODEL_SAVE);
                    }
                }
                else {
                    throw new Exception(self::ERR_DB_MODEL_INIT);
                }
            }
            catch (Exception $e) {
                $this->_dbModelErrors = $e->getMessage();
                throw new Exception(self::ERR_DB_MODEL_SAVE);
            }
        }
    }
    
    /**
     * Adds recipient with email address and name.
     * @see PHPMailer::addAddress()
     * @see _addEmails()
     * @param string $address recipient's email
     * @param string $name optional recipient's name
     */
    protected function _to($address, $name = '')
    {
        $this->getPhpmailer()->addAddress($address, $name);
        $this->_addEmails($address, $name);
    }
    
    /**
     * Sets alternative body (optionally) for initialised object.
     * You can skip this method for setContentType = html because AltBody is set
     * automatically when adding html body content.
     * @param string $altbody alternative plain text body
     * @return MultiMailer
     */
    public function altbody($altbody)
    {
        if ($this->_initState) {
            $this->getPhpmailer()->AltBody = $altbody;
        }
        
        return $this;
    }
    
    /**
     * Adds an attachment from a path on the filesystem for initialised object.
     * Returns false if the file could not be found or read.
     * @param string $path path to the attachment.
     * @param string $name overrides the attachment name.
     * @param string $encoding file encoding (options: "8bit", "7bit", "binary", 
     * "base64", and "quoted-printable").
     * @param string $type file extension (MIME) type.
     * @param string $disposition disposition to use
     * @see PHPMailer::addAttachment()
     * @since 1.4
     * @return boolean
     */
    public function attachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        if ($this->_initState) {
            
            try {
                return $this->getPhpmailer()->addAttachment($path, $name, $encoding, $type, $disposition);
            }
            catch (phpmailerException $e) {
                
                $this->setMultiError($e->errorMessage());
            }
            catch (Exception $e) {
                
                $this->setMultiError($e->getMessage());
            }
        }

        return false;
    }
    
    /**
     * Adds the list of attachments from a path on the filesystem for 
     * initialised object.
     * Returns false if one of the files could not be found or read.
     * @param array $attachments arrays of files data
     * @see attachment()
     * @see PHPMailer::addAttachment()
     * @since 1.4
     * @return boolean
     */
    public function attachments($attachments)
    {
        if ($this->_initState) {
            
            try {
                foreach ($attachments as $attachment) {
                    if (!$this->getPhpmailer()->addAttachment(
                            $attachment[0], 
                            !empty($attachment[1]) ? $attachment[1] : '', 
                            !empty($attachment[2]) ? $attachment[2] : 'base64', 
                            !empty($attachment[3]) ? $attachment[3] : '', 
                            !empty($attachment[4]) ? $attachment[4] : 'attachment'
                        )) {
                        
                        return false;
                    }
                }
                
                return true;
            }
            catch (phpmailerException $e) {
                
                $this->setMultiError($e->errorMessage());
            }
            catch (Exception $e) {
                
                $this->setMultiError($e->getMessage());
            }
        }

        return false;
    }
    
    /**
     * Adds blind carbon copy recipient with email address and name for 
     * initialised object.
     * Note that in case of DB method BCC recipients are treated as regular 
     * ones.
     * @see PHPMailer::addBCC()
     * @see _bcc()
     * @param string $address BCC recipient's email address
     * @param string $name optional BCC recipient's name
     * @since 1.1
     * @return MultiMailer
     */
    public function bcc($address, $name = '')
    {
        if ($this->_initState) {
            $this->_bcc($address, $name);
        }
        
        return $this;
    }
    
    /**
     * Adds list of blind carbon copy recipients with email address and name for 
     * initialised object.
     * Array can contain only strings with email addresses or can contain arrays 
     * of email address and name of each recipient i.e.
     * array('email1@example.com', 'email2@example.com', array('email2@example.com', 'Example3'))
     * Note that in case of DB method BCC recipients are treated as regular ones.
     * @see PHPMailer::addBCC()
     * @see _bcc()
     * @param array $addresses bcc recipients data
     * @since 1.4
     * @return MultiMailer
     */
    public function bccs($addresses)
    {
        if ($this->_initState) {
            foreach ($addresses as $address) {
                if (is_array($address)) {
                    $this->_bcc($address[0], !empty($address[1]) ? $address[1] : '');
                }
                else {
                    $this->_bcc($address);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Sets email body with additional parameters for initialised object.
     * @see PHPMailer::msgHTML()
     * @param string|array $body email body
     * This is string for non-templated email or array for templated one.
     * @see _processBody()
     * @param string $basedir optional baseline directory for path
     * @param boolean $advanced whether to use the advanced HTML to text converter
     * @return MultiMailer
     */
    public function body($body, $basedir = '', $advanced = false)
    {
        if ($this->_initState) {
            $this->_body         = $body;
            $this->_bodyBasedir  = $basedir;
            $this->_bodyAdvanced = $advanced;
        }
        
        return $this;
    }
    
    /**
     * Adds carbon copy recipient with email address and name for 
     * initialised object.
     * Note that in case of DB method CC recipients are treated as regular ones.
     * @see PHPMailer::addCC()
     * @see _cc()
     * @param string $address CC recipient's email address
     * @param string $name optional CC recipient's name
     * @since 1.1
     * @return MultiMailer
     */
    public function cc($address, $name = '')
    {
        if ($this->_initState) {
            $this->_cc($address, $name);
        }
        
        return $this;
    }
    
    /**
     * Adds list of carbon copy recipients with email address and name for 
     * initialised object.
     * Array can contain only strings with email addresses or can contain arrays 
     * of email address and name of each recipient i.e.
     * array('email1@example.com', 'email2@example.com', array('email2@example.com', 'Example3'))
     * Note that in case of DB method CC recipients are treated as regular ones.
     * @see PHPMailer::addCC()
     * @see _cc()
     * @param array $addresses cc recipients data
     * @since 1.4
     * @return MultiMailer
     */
    public function ccs($addresses)
    {
        if ($this->_initState) {
            foreach ($addresses as $address) {
                if (is_array($address)) {
                    $this->_cc($address[0], !empty($address[1]) ? $address[1] : '');
                }
                else {
                    $this->_cc($address);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Clears all recipients.
     * @since 1.5
     */
    public function clearAllRecipients()
    {
        $this->getPhpmailer()->clearAllRecipients();
        $this->_addresses = array();
    }
    
    /**
     * Sets DB AR model properties.
     * This method is the helper for MultiMailer::$setDbModelColumns and allows 
     * to set single property with name $names and value $value or array of 
     * properties (if so $value is ignored) with array keys being model 
     * properties and array values being property flags.
     * @see _db()
     * @see _dbColumn()
     * @param array|string $names single property or array of properties
     * @param string|null|boolean $value property flag or value, if true column 
     * is prepared to be saved under the default name, if null or false column 
     * is skipped, string value is additional column value
     * @since 1.2
     * @return MultiMailer
     */
    public function db($names, $value = true)
    {
        if ($this->_initState) {
            $this->_db($names, $value);
        }
        
        return $this;
    }
    
    /**
     * Sets 'from' header.
     * @see PHPMailer::setFrom()
     * @param string $address sender's email address
     * @param string $name optional sender's name
     * @param boolean $auto sets 'reply to' header automatically to the same address if true
     * @param boolean $skipInit if true skips initiation check
     * @see setDefaultPHPMailerOptions()
     * @return MultiMailer
     */
    public function from($address, $name = '', $auto = true, $skipInit = false)
    {
        if ($skipInit || $this->_initState) {
            $this->getPhpmailer()->setFrom($address, $name, $auto);
        }
        
        return $this;
    }
    
    /**
     * Gets error messages.
     * If setTranslation() is true message will be translated with Yii::t().
     * @see Yii:t()
     * @param boolean $all if true returns array with error message and DB AR 
     * validation errors (only for DB method)
     * @param string $category Yii:t() parameter, default 'app'
     * @param array $params Yii:t() parameter, default array()
     * @param string|null $source Yii:t() parameter, default null
     * @param string|null $language Yii:t() parameter, default null
     * @return string|array error message(s)
     */
    public function getMultiError($all = false, $category = 'app', $params = array(), $source = null, $language = null)
    {
        if ($this->setTranslation) {
            $error = Yii::t($category, $this->_error, $params, $source, $language);
        }
        else {
            $error = $this->_error;
        }
        
        if ($all) {
            return array(
                'MultiMailer'   => $error,
                'DbModelErrors' => $this->_dbModelErrors
            );
        }

        return $error;
    }
    
    /**
     * Gets PHPMailer object.
     * @since 1.5
     * @return ProxyPHPMailer
     */
    public function getPhpmailer()
    {
        return $this->_phpmailer;
    }

    /**
     * Initialises the MultiMailer object.
     */
    public function init()
    {
        parent::init();
        $this->_initState = $this->_initMethod();
    }
    
    /**
     * Sets 'reply to' header.
     * This is optional method to use when 'reply to' address is different than
     * 'from' address. In any other case this is set automatically.
     * @see PHPMailer::addReplyTo()
     * @param string $address email address to reply to
     * @param string $name optional name to reply to
     * @param boolean $skipInit if true skips initiation check
     * @see setDefaultPHPMailerOptions()
     * @return MultiMailer
     */
    public function replyto($address, $name = '', $skipInit = false)
    {
        if ($skipInit || $this->_initState) {
            $this->getPhpmailer()->addReplyTo($address, $name);
        }
        
        return $this;
    }
    
    /**
     * Prepares email body and sends (or saves) email
     * @see PHPMailer::Send()
     * @return boolean whether email has been sent (or saved)
     */
    public function send()
    {
        if ($this->_initState) {
            
            try {
                $this->_processBody();
                
                if (strtoupper($this->setMethod) == 'DB') {
                    $this->_saveModel();
                }
                else {
                    $this->getPhpmailer()->send();
                }
                
                return true;
            }
            catch (phpmailerException $e) {
                
                $this->setMultiError($e->errorMessage());
            }
            catch (Exception $e) {
                
                $this->setMultiError($e->getMessage());
            }
        }

        return false;
    }
    
    /**
     * Default options for PHPMailer.
     * Sets some headers.
     * @see from()
     * @see replyTo()
     * @see PHPMailer::isHTML()
     * @see PHPMailer::isSMTP()
     * Some of the options explained by PHPMailer:
     * SMTPDebug
     *  0 = off (for production use)
     *  1 = client messages
     *  2 = client and server messages
     * Debugoutput 'html' Ask for HTML-friendly debug output.
     * Host 'mail.example.com' Set the hostname of the mail server.
     * Port 25 Set the SMTP port number - likely to be 25, 465 or 587.
     * SMTPAuth true Whether to use SMTP authentication
     * Username 'yourname@example.com' Username to use for SMTP authentication
     * Password 'yourpassword' Password to use for SMTP authentication
     */
    public function setDefaultPHPMailerOptions()
    {
        $this->getPhpmailer()->CharSet     = 'UTF-8';
        $this->getPhpmailer()->Mailer      = 'mail';
        $this->getPhpmailer()->SMTPDebug   = 0;
        $this->getPhpmailer()->Debugoutput = 'html';
        $this->getPhpmailer()->Host        = 'mail.example.com';
        $this->getPhpmailer()->Port        = 25;
        $this->getPhpmailer()->SMTPAuth    = true;
        $this->getPhpmailer()->Username    = 'yourname@example.com';
        $this->getPhpmailer()->Password    = 'yourpassword';
        
        if ($this->setContentType == 'html') {
            $this->getPhpmailer()->isHTML();
        }
        
        if ($this->setPattern !== 'auto') {
            ProxyPHPMailer::$patternSelect = $this->setPattern;
        }
        
        if (!empty($this->setFromAddress)) {
            $address = $this->setFromAddress;
            $name    = !empty($this->setFromName) ? $this->setFromName : '';
            $this->from($address, $name, true, true);

            if ($this->setSameReply === true) {
                $this->replyto($address, $name, true);
            }
        }
        
        if ($this->setSameReply === false && !empty($this->setReplyAddress)) {
            $address = $this->setReplyAddress;
            $name    = !empty($this->setReplyName) ? $this->setReplyName : '';
            $this->replyto($address, $name, true);
        }
    }
    
    /**
     * Sets the language for PHPMailer error messages.
     * Returns false if it cannot load the language file.
     * The default language is English.
     * @param string $langcode ISO 639-1 2-character language code
     * @param string $lang_path Path to the language file directory, with 
     * trailing separator (slash)
     * You can set global $setLanguage parameter with just the $langcode or the 
     * array with $langcode and $lang_path.
     * @return boolean
     */
    public function setLanguage($langcode = 'en', $lang_path = '')
    {
        if (!is_null($this->getPhpmailer())) {
            $changeLanguage = false;
            if ($langcode !== 'en' || $lang_path !== '') {
                $changeLanguage = true;
            }
            else {
                if ($this->setLanguage !== '') {
                    if (is_array($this->setLanguage)) {
                        if (isset($this->setLanguage[0]) && $this->setLanguage[0] !== '') {
                            $langcode = $this->setLanguage[0];
                            $changeLanguage = true;
                        }
                        if (isset($this->setLanguage[1]) && $this->setLanguage[1] !== '') {
                            $lang_path = $this->setLanguage[1];
                            $changeLanguage = true;
                        }
                    }
                    else {
                        $langcode = $this->setLanguage;
                        $changeLanguage = true;
                    }
                }
            }
            if ($changeLanguage) {
                if (!$this->getPhpmailer()->setLanguage($langcode, $lang_path)) {
                    $this->setMultiError(self::ERR_LANG_NOT_FOUND);
                    return false;
                }
            }
        }
        else {
            $this->setMultiError(self::ERR_PHPMAILER_NOT_SET);
            return false;
        }
        
        return true;
    }
    
    /**
     * Sets and logs (optionally) error message.
     * @param string $error error message
     * @see Yii::log
     */
    public function setMultiError($error)
    {
        $this->_error = $error;
        if ($this->setLogging) {
            Yii::log($error, 'error', self::LOG_CATEGORY);
        }
    }
    
    /**
     * Sets default options and headers for PHPMailer.
     * @see setDefaultPHPMailerOptions()
     * Sets additional options for PHPMailer.
     * You can set here any option that PHPMailer allows.
     * @see $setOptions
     * Additional option overwrites the default one of the same name.
     */
    public function setPHPMailerOptions()
    {
        $this->setDefaultPHPMailerOptions();
        
        foreach ($this->setOptions as $name => $value) {
            $this->getPhpmailer()->$name = $value;
        }
        
        $this->setLanguage();
    }
    
    /**
     * Sets email subject for initialised object.
     * @param string $subject email subject
     * @return MultiMailer
     */
    public function subject($subject)
    {
        if ($this->_initState) {
            $this->getPhpmailer()->Subject = $subject;
        }
        
        return $this;
    }
    
    /**
     * Sets email template for initialised object.
     * @param string $template view name
     * @see Yii::renderPartial()
     * @see Yii::renderFile()
     * @return MultiMailer
     */
    public function template($template)
    {
        if ($this->_initState) {
            $this->_template = $template;
        }
        
        return $this;
    }
    
    /**
     * Adds recipient with email address and name for initialised object.
     * @see PHPMailer::addAddress()
     * @see _to()
     * @param string $address recipient's email address
     * @param string $name optional recipient's name
     * @return MultiMailer
     */
    public function to($address, $name = '')
    {
        if ($this->_initState) {
            $this->_to($address, $name);
        }
        
        return $this;
    }
    
    /**
     * Adds list of recipients with email address and name for initialised 
     * object.
     * Array can contain only strings with email addresses or can contain arrays 
     * of email address and name of each recipient i.e.
     * array('email1@example.com', 'email2@example.com', array('email2@example.com', 'Example3'))
     * @see PHPMailer::addAddress()
     * @see _to()
     * @param array $addresses recipients data
     * @since 1.4
     * @return MultiMailer
     */
    public function tos($addresses)
    {
        if ($this->_initState) {
            foreach ($addresses as $address) {
                if (is_array($address)) {
                    $this->_to($address[0], !empty($address[1]) ? $address[1] : '');
                }
                else {
                    $this->_to($address);
                }
            }
        }
        
        return $this;
    }
}