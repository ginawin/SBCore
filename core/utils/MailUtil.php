<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/10/2018
 * Time: 3:26 PM
 */

class MailUtil extends Util
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Send email
     * @param  array $toEmail
     * $toEmail['add'][]  = array('name'=>'text','email'=>'gina@gmail.com')
     * $toEmail['cc'][]   = array('name'=>'text','email'=>'gina@gmail.com')
     * $toEmail['bcc'][]  = array('name'=>'text','email'=>'gina@gmail.com')
     * @param  array $file
     * $file[]  = array('path'=>'/file.doc','name'=>'FileName','encoding'=>'base64','type'=>'mp3')
     * @param  array $config, if null -> set default config
     * $config =
            array(
                'Host'=>"smtp.gmail.com",
                'Port'=>'465,
                'SMTPAuth'=> true,
                'CharSet'=>'UTF-8',
                'SMTPSecure'=>'ssl',
                'Username'=>SYSTEM_SEND_EMAIL,
                'Password'=>'Sbauto!@1103',
                'SetFromEmail'=> SYSTEM_SEND_EMAIL,
                'SetFromName'=> 'IT Division',
                'WordWrap'=>'50',
                'IsHTML'=>true
            )
     * @param  string  $title - Email Title.
     * @param  string  $content - Email Content.
     * @return boolean
     */
    public function send($email,$title,$content,$config = null,$file = null){
        
        // Email, Title, Content null OR Email <> array() => return false;
        if(empty($email)||empty($title)||empty($content)||!is_array($email))
        {
            return false;
        }
    
        // Config <> array  và config <> null => return false; (Config <> null because Config = Config default)
        if(is_array($config) && !$config === null) {
            return false;
        }
    
        App::loadFile(CORE_DOCROOT."vendor/PHPMailer/PHPMailerAutoload.php");
        
        $mail = new PHPMailer(true);
        try {
            $mail->IsSMTP(); // set mailer to use SMTP
    
    
            $default_config =
                array(
                    'Host'=>"smtp.gmail.com",
                    'Port'=>'465',
                    'SMTPAuth'=> true,
                    'CharSet'=>'UTF-8',
                    'SMTPSecure'=>'ssl',
                    'Username'=>SYSTEM_SEND_EMAIL,
                    'Password'=>SYSTEM_SEND_PASSWORD,
                    'SetFromEmail'=> SYSTEM_SEND_EMAIL,
                    'SetFromName'=> SYSTEM_SEND_NAME,
                    'WordWrap'=>'50',
                    'IsHTML'=>true
                );
    
            if(empty($config)){
                $config = $default_config;
            }
    
            // Check Key exist
            $checkConfig = array('Host','Port','SMTPAuth','CharSet','SMTPSecure','WordWrap','IsHTML');
            foreach ($checkConfig as $keyName) {
                if(!array_key_exists($keyName,$config)||isset($config[$keyName])||empty($config[$keyName])){
                    $config[$keyName] = $default_config[$keyName];
                }
            }
    
            if(empty($config['Username'])||empty($config['Password'])){
                $config['Username'] = $default_config['Username'];
                $config['Password'] = $default_config['Password'];
            }
    
            if(empty($config['SetFromEmail'])||empty($config['SetFromName'])){
                $config['SetFromEmail'] = $default_config['SetFromEmail'];
                $config['SetFromName'] = $default_config['SetFromName'];
            }
    
    
    
            $mail->Host = $config['Host']; // specify main and backup server
            $mail->Port = $config['Port']; // set the port to use
            $mail->SMTPAuth = $config['SMTPAuth']; // turn on SMTP authentication
            $mail->CharSet = $config['CharSet'];
            $mail->SMTPSecure = $config['SMTPSecure'];
            $mail->Username = $config['Username'];
            $mail->Password = $config['Password'];
            $mail->SetFrom($config['SetFromEmail'], $config['SetFromName']);
            $mail->WordWrap = $config['WordWrap']; // set word wrap
            $mail->IsHTML($config['IsHTML']); // send as HTML
    
            $mail->Subject = $title;
            $mail->Body = $content;
    
            if(!empty($email)){
                foreach($email as $key => $val){
                    foreach($val as $k => $v){
                        $temp = "addAddress";
                        if($key == "cc"){
                            $temp = "addCC";
                        }else if($key == "bcc"){
                            $temp = "addBCC";
                        }
                        if(isset($v['email']) && !empty($v['email'])){
                            $mail->{$temp}($v['email'],(isset($v['name']) && !empty($v['name']))?$v['name']:"");
                        }
                    }
                }
            }
            $mail->addCC('tanthanh@ginawin.com','tanthanh@ginawin.com');
    
            if(!empty($file)){
                foreach($file as $key => $val){
                    if(isset($val['path']) && !empty($val['path'])){
                        $mail->addAttachment($val['path']);
                    }
                }
            }
            return $mail->Send();
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}