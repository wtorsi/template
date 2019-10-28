<?php

declare(strict_types=1);

namespace Cms\Catalog\Controller;

use Catalog\Entity\Product;
use Catalog\Provider\ProductProvider;
use Cms\Catalog\Form\Dto\ProductDto;
use Cms\Catalog\Form\Type\ProductType;
use Cms\Catalog\Processor\ProductProcessor;
use Dev\CmsBundle\Controller\AbstractController;
use Dev\CmsBundle\Form\Dto\DeleteDto;
use Dev\CmsBundle\Form\Type\DeleteType;
use Dev\MenuBundle\Menu\MenuBuilderInterface;
use Dev\MenuBundle\Navigation\ClosureNavigation;
use Dev\MenuBundle\Navigation\NavigationInterface;
use Dev\MetaBundle\Cms\Form\Dto\MetaDto;
use Dev\MetaBundle\Cms\Form\Type\MetaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("", name="cms_product_index", methods={"GET"})
     *
     * @param ProductProvider $provider
     *
     * @return Response
     */
    public function index(ProductProvider $provider): Response
    {
        $pagination = $this->getPagination();
        $entities = $provider->list(['sortOrder' => 'ASC'], $pagination);
        $deleteForm = $this->createDeleteForm();

        return $this->render('cms/catalog/product/index.html.twig', [
            'entities' => $entities,
            'pagination' => $pagination->createView(),
            'nav' => $this->createNavigation(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/create", name="cms_product_create", methods={"GET", "POST"})
     *
     * @param Request          $request
     * @param ProductProcessor $processor
     *
     * @return Response
     */
    public function create(Request $request, ProductProcessor $processor): Response
    {
        $dto = new ProductDto();
        $form = $this->createForm(ProductType::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            return $this->createSubmittedFormResponse($form, $request, function () use ($dto, $processor): JsonResponse {
                $entity = $processor->create($dto);

                return $this->createSuccessRedirectToRouteResponse('cms_product_edit', [
                    'id' => $entity->getId(),
                ]);
            });
        }

        return $this->render('cms/catalog/product/create.html.twig', [
            'form' => $form->createView(),
            'nav' => $this->createNavigation(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="cms_product_edit", methods={"GET", "POST"})
     *
     * @param Request          $request
     * @param Product          $entity
     * @param ProductProcessor $processor
     *
     * @return Response
     */
    public function edit(Request $request, Product $entity, ProductProcessor $processor): Response
    {
        $dto = new ProductDto($entity);
        $form = $this->createForm(ProductType::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            return $this->createSubmittedFormResponse($form, $request, function () use ($entity, $dto, $processor): JsonResponse {
                $processor->update($dto, $entity);

                return $this->createSuccessRedirectToRouteResponse('cms_product_edit', [
                    'id' => $entity->getId(),
                ]);
            });
        }

        return $this->render('cms/catalog/product/edit.html.twig', [
            'form' => $form->createView(),
            'nav' => $this->createNavigation($entity),
        ]);
    }

    /**
     * @Route("/meta/{id}", name="cms_product_meta", methods={"GET", "POST"})
     *
     * @param Request          $request
     * @param Product          $entity
     * @param ProductProcessor $processor
     *
     * @return Response
     */
    public function meta(Request $request, Product $entity, ProductProcessor $processor): Response
    {
        $dto = new MetaDto($entity);
        $form = $this->createForm(MetaType::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            return $this->createSubmittedFormResponse($form, $request, function () use ($entity, $dto, $processor): JsonResponse {
                $processor->update($dto, $entity);

                return $this->createSuccessRedirectToRouteResponse('cms_product_meta', [
                    'id' => $entity->getId(),
                ]);
            });
        }

        return $this->render('cms/catalog/product/edit.html.twig', [
            'form' => $form->createView(),
            'nav' => $this->createNavigation($entity),
        ]);
    }

    /**
     * @Route("/delete", name="cms_product_delete", methods={"DELETE"})
     *
     * @param Request          $request
     * @param ProductProcessor $processor
     *
     * @return Response
     */
    public function delete(Request $request, ProductProcessor $processor): Response
    {
        $form = $this->createDeleteForm($dto = new DeleteDto());

        return $this->createFormResponse($form, $request, function () use ($dto, $processor): JsonResponse {
            $processor->delete($dto);

            return $this->createSuccessRedirectToRouteResponse('cms_product_index');
        });
    }

    private function createDeleteForm(?DeleteDto $dto = null): FormInterface
    {
        return $this->createForm(DeleteType::class, $dto, [
            'class' => Product::class,
            'action' => $this->generateUrl('cms_product_delete'),
        ]);
    }

    private function createNavigation(?Product $entity = null): NavigationInterface
    {
        return new ClosureNavigation(function (MenuBuilderInterface $root) use ($entity): void {
            $root
                ->add('common.nav.index', ['route' => 'cms_product_index'])
                ->add('common.nav.create', ['route' => 'cms_product_create']);

            if (null !== $entity) {
                $root
                    ->add('common.nav.edit', ['route' => 'cms_product_edit', 'route_params' => ['id' => $entity->getId()]])
                    ->add('common.nav.blocks', ['route' => 'cms_product_block_index', 'route_params' => ['parent' => $entity->getId()]])
                    ->add('common.nav.meta', ['route' => 'cms_product_meta', 'route_params' => ['id' => $entity->getId()]]);
            }
        });
    }
}
