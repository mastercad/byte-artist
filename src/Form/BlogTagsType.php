<?php

namespace App\Form;

use App\Entity\BlogTags;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('blogFk')
//            ->add('blog', CollectionType::class, [
//                'entry_type' => BlogType::class,
//                'allow_add' => true
//            ])
//            ->add('tagFk')
//            ->add('tag', CollectionType::class, [
//                'entry_type' => TagsType::class,
//                'allow_add' => true
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlogTags::class,
        ]);
    }
}
