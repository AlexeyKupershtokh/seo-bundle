<?php

namespace Vesax\SEOBundle\Admin;

use Doctrine\Common\Cache\ClearableCache;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\CallbackTransformer;
use Vesax\SEOBundle\Form\Type\ExtraDataType;
use Vesax\SEOBundle\Form\Type\MetaTagType;
use Vesax\SEOBundle\Form\Type\UrlPatternType;

/**
 * Class RedirectRuleAdmin
 *
 * @author Artur Vesker
 */
class RedirectRuleAdmin extends Admin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'priority',
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('sourceTemplate')
            ->add('destination')
            ->add('code')
            ->add('priority')
            ->add('stopped')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('sourceTemplate')
            ->add('destination')
            ->addIdentifier('code')
            ->addIdentifier('priority')
            ->add('stopped')
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('sourceTemplate', new UrlPatternType(), ['sonata_help' => $this->getPatternHelp()])
            ->add('destination', 'text', ['required' => false, 'sonata_help' => 'Новый адрес'])
            ->add('code', 'choice', ['choices' => [
                '201' => '201 Created',
                '301' => '301 Moved Permanently',
                '302' => '302 Found',
                '303' => '303 See Other',
                '307' => '307 Temporary Redirect',
                '308' => '308 Permanent Redirect'
            ]])
            ->add('stopped')
            ->add('priority', 'text', ['sonata_help' => 'Приоритет правила. Если url соответстует нескольким правилам, то применяется правило с наибольшим приоритетом'])
        ;
    }

    /**
     * @return string
     *
     * TODO: get description from syntax handlers
     */
    private function getPatternHelp()
    {
        return "Шаблон url, для которого будет выполняться это правило. Новые правила приоритетнее старых.<br>Поддерживаемые маркеры:<ul><li>{*} - любая последовательность любой длины. Пример: /news/{*}</li></ul>
        Добавление/обновление/удаление правила вызовет очистку кеша. Для сохранения производительности сайта рекомендуется обновлять правило пакетно.";
    }

    /**
     * @param mixed $object
     * @return mixed|void
     */
    public function postUpdate($object)
    {
        $this->clearCache();
    }

    /**
     * @param mixed $object
     * @return mixed|void
     */
    public function postPersist($object)
    {
        $this->clearCache();
    }

    /**
     * @param mixed $object
     * @return mixed|void
     */
    public function postRemove($object)
    {
        $this->clearCache();
    }

    /**
     * Clear metadata cache
     */
    private function clearCache()
    {
        $container = $this->getConfigurationPool()->getContainer();

        if ($container->has('vesax.seo.metadata_cache')) {
            $cache = $container->get('vesax.seo.metadata_cache');

            if ($cache instanceof ClearableCache) {
                $cache->deleteAll();
            }
        }
    }


}
