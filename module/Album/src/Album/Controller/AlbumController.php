<?php
namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;
use Album\Form\AlbumForm;

class AlbumController extends AbstractActionController
{
    protected $albumTable;
    public function indexAction()
    {
        return new ViewModel(array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()){
                $album->exchangeArray($form->getData());
                $this->getAlbumTable()->saveAlbum($album);
                
                return $this->redirect()->toRoute('album');
            }
        }
        return array('form'=>$form);
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id',0);
        if ($id == 0){
            return $this->redirect()->toRoute('album', array('action' => 'add'));
        }
        
        try{
            $album = $this->getAlbumTable()->getAlbum($id);
        }catch (\Exception $e){
            echo $e;
            return $this->redirect()->toRoute('album', array('action' => 'index'));
        }
        
        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setValue('Edit');
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()){
                $this->getAlbumTable()->saveAlbum($album);
                
                return $this->redirect()->toRoute('album');
            }
        }
        return array(
            'id' => $id,
            'form'=>$form);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id',0);
        if ($id == 0){
            $this->redirect()->toRoute('album');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $del = $request->getPost('del','No');
            
            if ($del == 'Yes'){
                $this->getAlbumTable()->deleteAlbum($id);
            }
            return $this->redirect()->toRoute('album');
        }
        return  array(
            'id' => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        );  
    }

    public function getAlbumTable()
    {
        if(!$this->albumTable){
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }
}
