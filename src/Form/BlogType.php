<?php

namespace App\Form;

use App\Entity\Blogs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Titel',
                    'attr' => [
                        'placeholder' => 'Gib deinem Beitrag einen aussagekräftigen Titel ...',
                        'class' => 'form-control editor-title-input',
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Bitte gib einen Titel ein.']),
                        new Length(['min' => 3, 'max' => 255]),
                    ],
                ]
            )
            ->add(
                'shortDescription',
                TextareaType::class,
                [
                    'label' => 'Kurzbeschreibung',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Ein knapper Teaser-Text für Übersichtsseiten und Metadaten ...',
                        'class' => 'form-control',
                        'rows' => 3,
                    ],
                ]
            )
            ->add(
                'content',
                TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'tiptap-source',
                        'data-upload-url' => '/blog/upload',
                    ],
                ]
            )
            ->add(
                'seoLink',
                TextType::class,
                [
                    'label' => 'SEO-URL',
                    'attr' => [
                        'placeholder' => 'mein-beitrag-url-slug',
                        'class' => 'form-control',
                    ],
                ]
            )
            ->add(
                'previewPicture',
                HiddenType::class,
                ['required' => false]
            )
            ->add('blogTags', HiddenType::class, ['mapped' => false, 'required' => false])
            ->add('created', DateTimeType::class, [
                'label' => 'Datum',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blogs::class,
        ]);
    }
}
