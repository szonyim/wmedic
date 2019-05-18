<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createFormBuilder($user)
        ->add('userName', null, ['label' => 'Név'])
            ->add('phoneNumber', null, ['label' => 'Telefonszám'])
            ->add('email', EmailType::class, ['label' => 'Email cím'])
            ->add('password', PasswordType::class, ['label' => 'Jelszó'])
            ->add('roles', ChoiceType::class, ['choices' =>
                [
                    'Felhasználó' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'required' => true,
                'label' => 'Szerepkör'])

            ->getForm();


        //$form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try{
                $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                $user->setCreatedBy($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('user_index');
            }
            catch (UniqueConstraintViolationException $ucwe)
            {
                $form->addError(new FormError('Ez az email cím már foglalt!'));
            }
            catch (\Exception $e){
                $form->addError(new FormError('Váratlan hiba!' . $e->getMessage()));
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {

        $form = $this->createFormBuilder($user)
            ->add('userName', null, ['label' => 'Név'])
            ->add('phoneNumber', null, ['label' => 'Telefonszám'])
            ->add('email', EmailType::class, ['label' => 'Email cím'])
            ->add('newPassword', PasswordType::class, ['label' => 'Jelszó', 'required' => false])
            ->add('roles', ChoiceType::class, ['choices' =>
                [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'required' => true,
                'label' => 'Szerepkör'])

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if(strlen(trim($user->getPassword())) > 0)
            {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getNewPassword()));
            }


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
