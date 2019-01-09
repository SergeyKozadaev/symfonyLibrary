<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название',
            ])
            ->add('author', TextType::class, [
                'label' => 'Автор',
            ])
            ->add('addedDate', DateTimeType::class, [
                'label' => 'Дата прочтения',
                'choice_translation_domain' => 'forms',
            ])
            ->add('downloadable', CheckboxType::class, [
                'required' => false,
                'label' => 'Доступно для скачивания',
            ])
            ->add('coverImage', TextType::class, [
                'required' => false,
                'label' => 'Обложка: ',
            ])
            ->add('file', TextType::class, [
                'required' => false,
                'label' => 'Файл: ',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
