<?php
 
namespace Duyha\Contact\Controller\Magento\Contact\Index;
 
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Newsletter\Model\SubscriberFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
class Post extends \Magento\Contact\Controller\Index\Post
{
     
    private $dataPersistor;
    private $subscriberFactory;

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor
    ) {
        $this->subscriberFactory = $subscriberFactory;        
        parent::__construct(
            $context,
            $contactsConfig,
            $mail,
            $dataPersistor
        );
    }
     
    public function execute()
    {
         
        $post = $this->getRequest()->getPostValue();
         
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }
         
        //$this->inlineTranslation->suspend();
        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);
             
            $error = false;
          
             
            //$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            // $transport = $this->_transportBuilder
            // ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            // ->setTemplateOptions(
            //         [
            //                 'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            //                 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            //         ]
            //         )
            //         ->setTemplateVars(['data' => $postObject])
            //         ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
            //         ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            //         ->setReplyTo($post['email'])
            //         ->getTransport();
                     
            //         $transport->sendMessage();
                    //$this->inlineTranslation->resume();



           

                


                    $this->sendEmail($this->validatedParams()); 
                     //check subscribe:
                    if(isset($post['newsletter']) && $post['newsletter']){
                        $subscriber = $this->subscriberFactory->create()->loadByEmail($post['email']);
                        if ($subscriber->getId()
                            && (int) $subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED
                        ) {
                            // throw new LocalizedException(
                            //     __('This email address is already subscribed.')
                            // );
                        }else{
                            $subscriber->setFirstNameasd($post['name']);
                            $status = (int) $subscriber->subscribe($post['email'],$post['name'],$post['lastName']);    
                            
                            $subscriber->save();
                        }
                    }


                    $message = __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.');
                             
                    $this->getDataPersistor()->clear('contact_us');
                    echo $message;
                    //$this->_redirect('contact/index');
                    return;
        } catch (\Exception $e) {
            //$this->inlineTranslation->resume();
            $message = 
                    __('We can\'t process your request right now. Sorry, that\'s all we know: '.$e->getMessage());
                     
            $this->getDataPersistor()->set('contact_us', $post);
            //$this->_redirect('contact/index');
            echo $message;
            return;
        }
    }
     

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }

    private function getDataPersistor()
    {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
            ->get(DataPersistorInterface::class);
        }
         
        return $this->dataPersistor;
    }
}    