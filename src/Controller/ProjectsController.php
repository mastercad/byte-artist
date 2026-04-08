<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projects;
use App\Entity\ProjectTags;
use App\Entity\Tags;
use App\Form\ProjectsType;
use App\Repository\ProjectsRepository;
use App\Service\Pagination;
use App\Service\Seo\Generator\LinkFactory;
use DateTime;
use DirectoryIterator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\File\Base64EncodedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectsController extends AbstractController
{
    public const ITEMS_PER_PAGE = 10;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/projects', name: 'projects')]
    public function indexAction(
        Request $request,
        Pagination $pagination
    ): Response {
        $projectTags = $this->em->getRepository(ProjectTags::class)->findAll();

        /** @var ProjectsRepository $projectsRepository */
        $projectsRepository = $this->em->getRepository(Projects::class);
        $query = $projectsRepository->queryAllVisibleProjects();
        $projects = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);

        return $this->render(
            'projects/index.html.twig',
            [
                'projects' => $projects,
                'projectTags' => $projectTags,
                'lastPage' => $pagination->lastPage($projects),
            ]
        );
    }

    #[Route('/project/create', name: 'project_create', methods: ['GET'])]
    public function createAction(): Response
    {
        $project = new Projects();
        $project->setCreated(new \DateTime());
        $project->setCreator($this->getUser());

        $this->denyAccessUnlessGranted('edit', $project);

        $form = $this->createForm(ProjectsType::class, $project);

        $tags = $this->em->getRepository(Tags::class)->findAll();

        return $this->render('projects/create.html.twig', [
            'form' => $form->createView(),
            'id' => null,
            'availableTags' => $tags,
            'publicPicturePath' => $this->generatePublicPicturePath(),
        ]);
    }

    #[Route('/project/gallery', name: 'app_project_image_browser', methods: ['GET'])]
    public function imageBrowserAction(Request $request): Response
    {
        $publicUploadPath = $this->generatePublicUploadPath();
        $rootPath = __DIR__.'/../../public';
        $targetPath = $rootPath.$publicUploadPath;

        $fileIterator = new DirectoryIterator($targetPath);
        $files = [];

        foreach ($fileIterator as $file) {
            if (!$file->isDot()) {
                $files[] = $publicUploadPath.'/'.$file;
            }
        }

        return $this->render(
            'fragment/thumb_gallery.html.twig',
            [
                'images' => $files,
            ]
        );
    }

    #[Route('/project/delete/{projectId}', name: 'app_project_delete', methods: ['GET'], requirements: ['projectId' => '\d+'])]
    public function deleteAction(int $projectId): JsonResponse
    {
        $project = $this->em->getRepository(Projects::class)->find($projectId);

        $this->denyAccessUnlessGranted('delete', $project);

        $this->em->remove($project);
        $this->em->flush();

        $imagesPath = __DIR__.'/../../public'.$this->generatePublicPicturePath($projectId);

        if (is_dir($imagesPath)) {
            $directoryIterator = new DirectoryIterator($imagesPath);

            foreach ($directoryIterator as $file) {
                if ($file->isFile()) {
                    unlink($file->getPathname());
                }
            }
            rmdir($imagesPath);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route('/project/tag/{tagSeoLink}', name: 'project_tag_landing', methods: ['GET'], requirements: ['tagSeoLink' => '[a-z0-9\_\-]+'])]
    public function tagAction(
        Request $request,
        Pagination $pagination,
        string $tagSeoLink
    ): Response {
        $projectTags = $this->em->getRepository(ProjectTags::class)->findAll();

        /** @var ProjectsRepository $projectsRepository */
        $projectsRepository = $this->em->getRepository(Projects::class);
        $query = $projectsRepository->queryAllProjectsByTag($tagSeoLink);
        $projects = $pagination->paginate($query, $request, self::ITEMS_PER_PAGE);

        return $this->render(
            'projects/index.html.twig',
            [
                'projects' => $projects,
                'projectTags' => $projectTags,
                'lastPage' => $pagination->lastPage($projects),
                'tagSeoLink' => $tagSeoLink,
            ]
        );
    }

    #[Route('/project/create/{projectId}', name: 'project_edit_by_id', methods: ['GET', 'POST'], requirements: ['projectId' => '\d+'])]
    public function editByIdAction(int $projectId): Response
    {
        $project = $this->em->getRepository(Projects::class)->find($projectId);

        $this->denyAccessUnlessGranted('edit', $project);

        $form = $this->createForm(ProjectsType::class, $project);

        $tags = $this->em->getRepository(Tags::class)->findAll();

        return $this->render('projects/create.html.twig', [
            'form' => $form->createView(),
            'id' => $project->getId(),
            'availableTags' => $tags,
            'publicPicturePath' => $this->generatePublicPicturePath(),
        ]);
    }

    #[Route('/project/create/{projectSeoName}', name: 'project_edit_by_name', methods: ['GET', 'POST'], requirements: ['projectSeoName' => '[a-z0-9\_\-]+'])]
    public function editBySeoNameAction(string $projectSeoName): Response
    {
        $project = $this->em->getRepository(Projects::class)->findOneBy(['seoLink' => $projectSeoName]);

        $this->denyAccessUnlessGranted('edit', $project);

        $form = $this->createForm(ProjectsType::class, $project);

        $tags = $this->em->getRepository(Tags::class)->findAll();

        return $this->render('projects/create.html.twig', [
            'form' => $form->createView(),
            'id' => $project->getId(),
            'availableTags' => $tags,
            'publicPicturePath' => $this->generatePublicPicturePath(),
        ]);
    }

    #[Route('/project/save', name: 'project_save', methods: ['POST'])]
    public function saveAction(Request $request, LinkFactory $seoLinkFactory): JsonResponse
    {
        $projectId = $request->get('projects')['id'];

        $project = new Projects();
        $project->setCreated(new DateTime());
        $project->setCreator($this->getUser());

        if (0 < $projectId) {
            $project = $this->em->getRepository(Projects::class)->find($projectId);
            $project->setModified(new DateTime());
            $project->setModifier($this->getUser());
        }

        $this->denyAccessUnlessGranted('edit', $project);

        $projectRequest = $request->request->get('projects');
        $currentProjectTagsRequest = [];

        if (is_array($projectRequest) && array_key_exists('projectTags', $projectRequest)) {
            $currentProjectTagsRequest = $projectRequest['projectTags'];
            unset($projectRequest['projectTags']);
        }

        $project->setName($projectRequest['name']);

        $seoLinkGenerator = $seoLinkFactory->create(Projects::class, 'name');
        $seoLinkGenerator->extendWithSeoLink($project);

        $projectRequest['seoLink'] = $project->getSeoLink();
        $request->request->set('projects', $projectRequest);

        $form = $this->createForm(ProjectsType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse($this->extractErrorsFromForm($form));
        }

        $seoLinkGenerator->extendWithSeoLink($project);
        $this->em->persist($project);
        $this->em->flush();

        $this->handleUploadFiles($project);
        $this->clearUploadFolder();

        $this->em->flush();

        $project = $this->considerTags($project, $currentProjectTagsRequest);
        $projectRequest['id'] = $project->getId();

        return new JsonResponse($projectRequest);
    }

    private function handleUploadFiles(Projects $project): static
    {
        $regex = '/\<img .*? src="(?:http[s]*:\/\/[0-9\.a-z:]+)*(\/images\/upload\/'.$this->getUser()->getId().
            '\/projects\/([^"]+\.[a-z]+))" .*?\/>/i';

        if (!empty($project->getDescription())
            && preg_match_all($regex, $project->getDescription(), $matches)
        ) {
            foreach ($matches[1] as $filePathname) {
                $absoluteFilePath = __DIR__.'/../../public'.$filePathname;

                if (!file_exists($absoluteFilePath)) {
                    continue;
                }

                $file = new File($absoluteFilePath);
                if ($file->isReadable()) {
                    $targetPublicPath = '/images/content/dynamisch/projects/'.$project->getId().'/';
                    $file->move(__DIR__.'/../../public'.$targetPublicPath);
                    $newImagePath = str_replace(
                        $filePathname,
                        $targetPublicPath.basename($filePathname),
                        $project->getDescription()
                    );
                    $project->setDescription($newImagePath);
                }
            }
        }

        $previewFilePath = __DIR__.'/../../public/'.$project->getPreviewPicture();
        if (file_exists($previewFilePath)) {
            $file = new File($previewFilePath);
            $targetPublicPath = '/images/content/dynamisch/projects/'.$project->getId();
            $file->move(__DIR__.'/../../public/'.$targetPublicPath, basename($previewFilePath));
            $project->setPreviewPicture($targetPublicPath.'/'.basename($previewFilePath));
        }

        return $this;
    }

    private function clearUploadFolder(): static
    {
        $folderPath = __DIR__.'/../../public/images/upload/'.$this->getUser()->getId().'/projects';
        $dirIterator = new DirectoryIterator($folderPath);

        foreach ($dirIterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }

        return $this;
    }

    private function considerTags(Projects $project, array $projectTags): Projects
    {
        $currentProjectTags = $this->em->getRepository(ProjectTags::class)->findBy(['project' => $project]);
        $oldProjectTags = [];
        foreach ($currentProjectTags as $currentProjectTag) {
            $oldProjectTags[$currentProjectTag->getId()] = $currentProjectTag;
        }

        foreach ($projectTags as $projectTag) {
            if (isset($projectTag['id'])
                && !empty($projectTag['id'])
                && 'undefined' !== $projectTag['id']
            ) {
                unset($oldProjectTags[$projectTag['id']]);
                continue;
            }

            if (isset($projectTag['tagId'])
                && !empty($projectTag['tagId'])
                && 'undefined' !== $projectTag['tagId']
            ) {
                $tagEntity = $this->em->getRepository(Tags::class)->find($projectTag['tagId']);
            } else {
                $tagEntity = new Tags();
                $tagEntity->setCreator($this->getUser());
                $tagEntity->setCreated(new DateTime());
                $tagEntity->setName($projectTag['tagName']);
                $tagEntity->setSeoLink(strtolower($projectTag['tagName']));
                $this->em->persist($tagEntity);
                $this->em->flush();
            }

            $projectTagEntity = new ProjectTags();
            $projectTagEntity->setCreated(new DateTime());
            $projectTagEntity->setCreator($this->getUser());
            $projectTagEntity->setTag($tagEntity);
            $projectTagEntity->setProject($project);
            $this->em->persist($projectTagEntity);
            $project->addProjectTag($projectTagEntity);
            $this->em->flush();
        }

        foreach ($oldProjectTags as $oldProjectTag) {
            $this->em->remove($oldProjectTag);
            $this->em->flush();
        }

        return $project;
    }

    #[Route('/project/upload', name: 'app_project_image_upload', methods: ['POST'])]
    public function uploadImageAction(Request $request): JsonResponse
    {
        $projectId = $request->get('id');

        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : []);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('upload');

        if (null === $file && null !== $request->get('fileData')) {
            $file = new Base64EncodedFile($request->get('fileData'));
            $originalFileName = $request->get('name');
        } else {
            $originalFileName = $file->getClientOriginalName();
        }

        $publicUploadPath = $this->generatePublicUploadPath();
        $targetPath = __DIR__.'/../../public'.$publicUploadPath;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0644, true);
        }

        $file->move($targetPath, $originalFileName);

        return new JsonResponse([
            'uploaded' => 1,
            'fileName' => $originalFileName,
            'url' => $publicUploadPath.'/'.$originalFileName,
        ]);
    }

    #[Route('/project/upload/preview', name: 'app_project_preview_upload', methods: ['POST'])]
    public function uploadPreviewAction(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('upload');
        $originalFileName = null;

        if (null === $file && $request->get('fileData')) {
            $file = new Base64EncodedFile($request->get('fileData'));
            $originalFileName = $request->get('name');
        } else {
            $originalFileName = $file->getClientOriginalName();
        }

        $publicUploadPath = $this->generatePublicUploadPath();
        $targetPath = __DIR__.'/../../public'.$publicUploadPath;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0644, true);
        }

        $fileName = 'preview.'.$file->guessExtension();
        $file->move($targetPath, $fileName);

        return new JsonResponse([
            'uploaded' => 1,
            'fileName' => $fileName,
            'url' => $publicUploadPath.'/'.$fileName,
        ]);
    }

    #[Route('/projects/upload/delete', name: 'app_project_delete_upload')]
    public function clearUploadFolderAction(): JsonResponse
    {
        $project = new Projects();
        $this->denyAccessUnlessGranted('edit', $project);

        $files = glob($this->getParameter('kernel.project_dir').'/public/'.$this->generatePublicUploadPath().'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/project/{projectId}', name: 'project_show_by_id', methods: ['GET'], requirements: ['projectId' => '\d+'])]
    public function showByIdAction(int $projectId): Response
    {
        $project = $this->em->getRepository(Projects::class)->find($projectId);

        $this->denyAccessUnlessGranted('show', $project);

        return $this->render('projects/show.html.twig', ['project' => $project]);
    }

    #[Route('/project/{projectSeoName}', name: 'project_show_by_name', methods: ['GET'], requirements: ['projectSeoName' => '[a-z0-9\_\-]+'])]
    public function showBySeoNameAction(string $projectSeoName): Response
    {
        $project = $this->em->getRepository(Projects::class)->findOneBy(['seoLink' => $projectSeoName]);

        $this->denyAccessUnlessGranted('show', $project);

        return $this->render('projects/show.html.twig', ['project' => $project]);
    }

    private function generatePublicPicturePath(?int $projectId = null): ?string
    {
        if ($projectId) {
            return '/images/content/dynamisch/projects/'.$projectId;
        }

        return null;
    }

    private function generatePublicUploadPath(): string
    {
        return '/images/upload/'.$this->getUser()->getId().'/projects';
    }

    /**
     * @psalm-return array<int|string, mixed>
     */
    private function extractErrorsFromForm(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->extractErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
