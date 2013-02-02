<?php
 
namespace Blog\Controller;
 
use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel, 
    Blog\Form\PostForm,
    Doctrine\ORM\EntityManager,
    Blog\Entity\Post;
 
class PostController extends AbstractActionController
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
 
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
 
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
 
    public function indexAction()
    {
        return new ViewModel(array(
            //'posts' => $this->getPostTable()->fetchAll()
            'posts' => $this->getEntityManager()->getRepository('Blog\Entity\Post')->findAll()
        ));
    }

    public function viewAction()
    {
        $id = (int)$this->getEvent()->getRouteMatch()->getParam('id');
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }
        return new ViewModel(array(
            //'post' => $this->getPostTable()->getPost($id)
            'post' => $this->getEntityManager()->find('Blog\Entity\Post', $id)
        ));
    }
 
    public function addAction()
    {
        $form = new PostForm();
        $form->get('submit')->setAttribute('label', 'Add');
 
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = new Post();
            
            $form->setInputFilter($post->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $post->exchangeArray($form->getData());
                //$this->getPostTable()->savePost($post);

                $this->getEntityManager()->persist($post);
                $this->getEntityManager()->flush();
 
                return $this->redirect()->toRoute('post'); 
            }
        }
 
        return array('form' => $form);
    }
 
    public function editAction()
    {
        $id = (int)$this->getEvent()->getRouteMatch()->getParam('id');
        if (!$id) {
            return $this->redirect()->toRoute('post', array('action'=>'add'));
        } 
        $post = $this->getEntityManager()->find('Blog\Entity\Post', $id);
 
        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('label', 'Edit');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($post->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                //$this->getPostTable()->savePost($post);
                $this->getEntityManager()->persist($post);
                $this->getEntityManager()->flush();

                return $this->redirect()->toRoute('post');
            }
        }
 
        return array(
            'id' => $id,
            'form' => $form,
        );
    }
 
    public function deleteAction()
    {
        $id = (int)$this->getEvent()->getRouteMatch()->getParam('id');
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }
 
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost()->get('del', 'No');
            if ($del == 'Yes') {
                //$id = (int)$request->getPost()->get('id');
                //$this->getPostTable()->deletePost($id);

                $id = (int)$request->getPost()->get('id');
                $post = $this->getEntityManager()->find('Blog\Entity\Post', $id);
                if ($post) {
                    $this->getEntityManager()->remove($post);
                    $this->getEntityManager()->flush();
                }
            }
 
            return $this->redirect()->toRoute('post', array(
                'controller' => 'post',
                'action'     => 'index',
            ));
        }
 
        return array(
            'id' => $id,
            //'post' => $this->getPostTable()->getPost($id)
            'post' => $this->getEntityManager()->find('Blog\Entity\Post', $id)
        );
    }
    
    /*protected $postTable;

    public function getPostTable()
    {
        if (!$this->postTable) {
            $sm = $this->getServiceLocator();
            $this->postTable = $sm->get('Blog\Model\PostTable');
        }
        return $this->postTable;
    }*/
}