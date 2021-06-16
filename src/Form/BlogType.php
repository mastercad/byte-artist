<?php

namespace App\Form;

use App\Entity\Blogs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('shortDescription')
            ->add('content')
            ->add('seoLink')
            ->add('previewPicture')
            ->add('group')
            ->add('groupOrder')
//            ->add('blogTags', CollectionType::class, [
//                'entry_type' => BlogTagsType::class,
//                'allow_add' => true
//            ])
            ->add('blogTags')
            ->add('created')
            ->add('modified')
            ->add('creator')
            ->add('modifier')
        ;
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Blogs::class,
        ]);
    }
}
