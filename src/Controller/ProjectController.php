<?php
// TODO: Keep namespace structure consistent with PSR-4 setup; verify that Api\Controller matches the configured autoloading strategy.
namespace Api\Controller;

use App\Model;
use App\Storage\DataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// TODO: Add supported return types to controller actions to make the HTTP contract clearer.
class ProjectController
{
    /**
     * @var DataStorage
     */
    private $storage;

    // TODO: Depend on an abstraction instead of the concrete DataStorage class to reduce controller-storage coupling.
    public function __construct(DataStorage $storage)
    {
        $this->storage = $storage;
    }

    // TODO: Keep JSON serialization strategy consistent; this controller currently mixes model-level toJson(), json_encode(), and JsonResponse.
    /**
     * @param Request $request
     *
     * @Route("/project/{id}", name="project", method="GET")
     */
    public function projectAction(Request $request)
    {
        try {
            $project = $this->storage->getProjectById($request->get('id'));

            return new Response($project->toJson());
        } catch (Model\NotFoundException $e) {
            return new Response('Not found', 404);
        } catch (\Throwable $e) {
            // TODO: Avoid catching \Throwable here without logging or centralized error handling.
            return new Response('Something went wrong', 500);
        }
    }

    // TODO: Avoid returning Response(json_encode(...)); use a dedicated JSON response strategy for API actions.
    // TODO: Validate id, limit, and offset before passing request data to the storage layer.
    /**
     * @param Request $request
     *
     * @Route("/project/{id}/tasks", name="project-tasks", method="GET")
     */
    public function projectTaskPagerAction(Request $request)
    {
        $tasks = $this->storage->getTasksByProjectId(
            $request->get('id'),
            $request->get('limit'),
            $request->get('offset')
        );

        return new Response(json_encode($tasks));
    }

    // TODO: Do not use $_REQUEST inside a Symfony controller; read data from the Request object only.
    // TODO: Handle missing project consistently; getProjectById() throws NotFoundException, so the null-check does not match the storage contract.
    // TODO: Do not pass raw request payload directly to the storage layer; whitelist and validate allowed fields first.
    /**
     * @param Request $request
     *
     * @Route("/project/{id}/tasks", name="project-create-task", method="PUT")
     */
    public function projectCreateTaskAction(Request $request)
    {
		$project = $this->storage->getProjectById($request->get('id'));
		if (!$project) {
			return new JsonResponse(['error' => 'Not found']);
		}

		return new JsonResponse(
			$this->storage->createTask($_REQUEST, $project->getId())
		);
    }
}
