<?php
namespace Mital\Careers\Controller\Index;
use Magento\Framework\App\Action\Context; 
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action
{   
    protected $_resultPageFactory;
    
    protected $_modelDataFactory;
 
    public function __construct(
        Context $context,       
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mital\Careers\Model\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    )
    {       	
        $this->_resultPageFactory = $resultPageFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);        
    }
    public function execute()
	{  

     try{
        $request = $this->getRequest()->getParams();    

        if(isset($_FILES['myfile']) && isset($_FILES['myfile']['name']) && strlen($_FILES['myfile']['name'])){
            $base_media_path = 'Mital/Careers';
            $uploader = $this->uploader->create(
                ['fileId' => 'myfile']
            );                
            $uploader->setAllowedExtensions(['pdf','doc','docx','odt']);               
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath($base_media_path)
            );                
            $data['myfile'] = $base_media_path.$result['file'];
            $filePath = $result['path'].$result['file'];
            $fileName = $result['name'];
         }

         $templateVars = [            
            'name'      => $request['your_name'],            
            'message'   => $request['your_message'],
            'email'     => $request['email_address'],
            'telephone' => $request['your_telephone']
        ];

        $fromEmail= $this->getRequest()->getPostValue('email_address');
        $fromName = $this->getRequest()->getPostValue('your_name');

        $from = ['email' => $fromEmail, 'name' => $fromName];
        $this->inlineTranslation->suspend();

        $to =  $this->_scopeConfig->getValue(
            'contact/email/recipient_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );         

         $templateOptions = [
          'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
          'store' => 1
        ];        

        if(isset($fileName)){
            $transport = $this->_transportBuilder->setTemplateIdentifier('careers_email_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->addAttachment(file_get_contents($filePath),$fileName ,'application/pdf')
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
        }else{
            $transport = $this->_transportBuilder->setTemplateIdentifier('careers_email_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)                
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        }
        
             
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        $this->messageManager->addSuccess(__('Form successfully submitted'));

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;

    }catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }           
           
	}
   
}
?>
