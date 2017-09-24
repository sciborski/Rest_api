<?php
namespace AppBundle\Form\Type;

use AppBundle\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('town')
            ->add('street')
            ->add('price')
            //->add('id_user')
            ->add('id_user','entity',array(
                'class' => 'AppBundle\Entity\User',
                'multiple' => false
            ))
            //->add('idProduct')
            ->add('id_product','entity',array(
                'class' => 'AppBundle\Entity\Product',
                'multiple' => false
            ))
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Location',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

}