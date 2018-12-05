<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['help' => 'some help text'])
            ->add('author')
            ->add('addedDate', DateTimeType::class, [
                'choice_translation_domain' => true,
                'data' => new \DateTime()
            ])
            ->add('downloadable', CheckboxType::class, ['required' => false])
            ->add('coverImage', FileType::class, [
                'required' => false,
                'label' => 'Обложка: '
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'label' => 'Файл: '
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class
        ]);
    }

}