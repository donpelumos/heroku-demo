 <?php
	//namespace pp\mail;
/*
* SEND EMAILS FOR REGISTERATION, PASSWORD RESET,ETC
* @var to, from body, subject
*/
 class RegMail{
	 
	 /**
     * determines if smpt is to be used or not.
     * @var bool
	 * @default true;
     */
	 
	 private $use_smtp = true;
	 
	 
	 /**
     * body of the message, usually allows html tags and design
     * @var string
     */
	 
	 private $body = '';
	 
	 /**
     * alt body of the message, plain text
     * @var string
     */
	 
	 private $alt_body = '';
	 
	 private $to ='';
	
	 /**
     * For storing multiple users
     * @var PHPMailer
     */
	 
	 public $subject = '';
	 
	 /**
     * For storing multiple users
     * @var PHPMailer
     */
	 
	 private $recipients = array();
    /**
     * Importing the php mailer.
     * @var PHPMailer
     */
     private $mail = '';
     
    /**
     * The HOST FOR SMTP PURPOSES.
     * @var string
     */
	 public $host = '';
	 
    /**
     * SMTP username.
     * @var string
     */
    public $username = '';

    /**
     * SMTP password.
     * @var string
     */
    public $password = '';

    /**
     * The From email address for the message.
     * @var string
     */
	 public $from = '';
	 
    /**
     * The From name for the message.
     * @var string
     */
	 public $from_name = '';
	 
	 
    /**
     * Initialise the mailing stuff
     * @param $use_smtp = 'true' (optional)
     */
	 
	 public function __construct($use_smtp = true){
		 $this->use_smtp = $use_smtp;
		 
		 $this->mail = new PHPMailer();
		 if($this->use_smtp == true){
		     $this->mail->isSMTP();
	     	$this->mail->smtpDebug = 1;
             $this->mail->SMTPAuth = true;
            //If SMTP requires TLS encryption then set it
            $this->mail->SMTPSecure = "tls";
            $this->mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
            //$this->mail->Port = 465;
            $this->mail->Port = 587;
		 }
		 
	 }
	 
    public function build(){
		if($this->use_smtp == true){
           $this->mail->Host = $this->host;
            $this->mail->Username = trim($this->username);
            $this->mail->Password = trim($this->password);
		}
        $this->mail->setFrom(trim($this->from), trim($this->from_name));
        //$this->mail->From = $this->from;
        //$this->mail->FromName = $this->from_name;
        $this->mail->Subject = $this->subject;
        //$mail->addReplyTo('list@example.com', 'List manager');
        $this->mail->isHtml(true);
    }
    function set_body ($body,$alt_body = '') {
		$this->alt_body = $alt_body;
		$this->body = $body;
        $this->mail->Body = $this->body;//stripslashes($body);
		$this->mail->AltBody =$this->alt_body;
    }

    function send_to ($to) {
        $this->mail->clearAddresses();
        $this->mail->addAddress($to);

        if (!$this->mail->send()) {
            //echo 'Mailer Error: ' . $this->mail->ErrorInfo;
        } else {
            return true;
        }
    }
	
	
    function send_tos ($recipients) {
       // $this->mail->clearAddresses();
        $this->mail->addAddress($to);

        if (!$this->mail->send()) {
             echo 'Mailer Error: ' . $this->mail->ErrorInfo;
        } else {
            return true;
        }
    }
    /**
     * TO add headers to the mail
     * @param $value should be like this e.g 'MIME-Version: 1.0'
     */
    function add_header($value){
        $value = isset($value) ? trim($value) : '';
        if(!empty($value)){
            $this->mail->addCustomHeader($value);
        }
    }
/*	
	 public function send_mail($to,$subject,$body){
		 $this->to = $to;
		// $this->from = $from;
		 $this->subject = "TBC Nigeria - ".$subject;
		 $this->body = $body;
		 
		 $mail_headers = "From no-reply@tbcnigeria.com.ng";
	 //main stuff       
//SEND TO THE MAIL
       mail($this->to,$this->subject,$this->body,$mail_headers);

	 }
	 */
 }
 