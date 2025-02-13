<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class RegistroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('nombre')
            ->add('apellido')
            // ->add('fecha_registro', null, [
            //     'widget' => 'single_text',
    // 'format' => 'yyyy-MM-dd HH:mm:ss', // Ajusta el formato según tus necesidades
            // ])
            // ->add('rol')
            ->add('fecha_nacimiento', null, [
                'widget' => 'single_text',
            ])
            ->add('localidad')
            ->add('biografia')
            // ->add('activacion_token')
            // ->add('verificado')
            ->add('submit', SubmitType::class, [
                'label' => 'Registrarse',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
