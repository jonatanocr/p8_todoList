<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateUserFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', null, array('label' => false));
        $builder->add('username', null, array('label' => false));
        if ($options['include_password']) {
            $builder->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => ['label' => false, 'hash_property_path' => 'password'],
                'second_options' => ['label' => false],
            ]);
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => ['User' => 'ROLE_USER', 'Admin' => 'ROLE_ADMIN'],
                'label' => false
            ]);
            $builder->get('roles')
                ->addModelTransformer(
                    new CallbackTransformer(
                        function ($rolesArray) {
                            // transform the array to a string
                            return count($rolesArray) ? $rolesArray[0] : null;
                        },
                        function ($rolesString) {
                            // transform the string back to an array
                            return [$rolesString];
                        }
                    )
                )
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'include_password' => true,
        ]);
    }
}