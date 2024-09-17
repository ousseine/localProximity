<?php

namespace App\Form;

use App\Entity\Option;
use App\Entity\Question;
use App\Entity\Survey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de la question',
                'empty_data' => '',
                'required' => false
            ])
            ->add('name', TextType::class, [
                'empty_data' => '',
            ])
            ->add('type', ChoiceType::class, [
                'empty_data' => '',
                'choices' => [
                    'text' => 'text',
                    'hidden' => 'hidden',
                    'textarea' => 'textarea',
                    'Choix multiple' => 'multiple_choice',
                    'Choix unique' => 'unique_choice',
                ],
                'autocomplete' => true,
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'Champ obligatoire',
                'required' => false
            ])
            ->add('options', OptionAutocompleteField::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
