<?php

namespace App\Form;

use App\Entity\Survey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

class SurveyType extends AbstractType
{
    public function __construct(private readonly SluggerInterface $slugger) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => '',
            ])
            ->add('description', TextType::class, [
                'empty_data' => '',
                'required' => false
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'data-controller' => 'form-collection',
                    'data-form-collection-add-label-value' => 'Ajouter une question',
                    'data-form-collection-delete-label-value' => 'Supprimer',
                ]
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-primary']
            ])
            ->addEventListener(FormEvents::SUBMIT, $this->autoSlug(...))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }

    private function autoSlug(FormEvent $event): void
    {
        /** @var Survey $survey */
        $survey = $event->getData();

        if ((null === $survey->getSlug() && null !== $survey->getTitle()) || (null !== $survey->getTitle())) {
            $slug = $this->slugger->slug($survey->getTitle())->lower();
            $survey->setSlug($slug);
        }
    }
}
