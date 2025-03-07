<?php

namespace App\Form;

use App\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'empty_data' => '',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Sauvegarder',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Sauvegarder et ajouter',
                'attr' => ['class' => 'btn btn-outline-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Option::class,
        ]);
    }
}
