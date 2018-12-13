<?php namespace Common\Auth\Controllers;

use App\User;
use Common\Settings\Settings;
use Illuminate\Http\Request;
use Common\Auth\UserRepository;
use Common\Core\Controller;
use Common\Auth\Requests\ModifyUsers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UsersController extends Controller {

    /**
     * @var User
     */
    private $model;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param User $user
     * @param UserRepository $userRepository
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(User $user, UserRepository $userRepository, Request $request, Settings $settings)
    {
        $this->model = $user;
        $this->request = $request;
        $this->userRepository = $userRepository;

        $this->middleware('auth', ['except' => ['show']]);
        $this->settings = $settings;
    }

    /**
     * Return a collection of all registered users.
     *
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', User::class);

        return $this->userRepository->paginateUsers($this->request->all());
    }

    /**
     * Return user matching given id.
     *
     * @param integer $id
     * @return User
     */
    public function show($id)
    {
        $relations = array_filter(explode(',', $this->request->get('with', '')));
        $relations = array_merge(['roles', 'social_profiles'], $relations);

        if ($this->settings->get('envato.enable')) {
            $relations[] = 'purchase_codes';
        }

        $user = $this->model->with($relations)->findOrFail($id);

        $this->authorize('show', $user);

        return $this->success(['user' => $user]);
    }

    /**
     * Create a new user.
     *
     * @param ModifyUsers $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ModifyUsers $request)
    {
        $this->authorize('store', User::class);

        $user = $this->userRepository->create($this->request->all());

        return $this->success(['user' => $user], 201);
    }

    /**
     * Update an existing user.
     *
     * @param integer $id
     * @param ModifyUsers $request
     *
     * @return User
     */
    public function update($id, ModifyUsers $request)
    {
        $user = $this->userRepository->findOrFail($id);

        $this->authorize('update', $user);

        $user = $this->userRepository->update($user, $this->request->all());

        return $this->success(['user' => $user]);
    }

    /**
     * Delete multiple users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMultiple()
    {
        $this->authorize('destroy', User::class);

        $this->validate($this->request, [
            'ids' => 'required|array|min:1'
        ]);

        $this->userRepository->deleteMultiple($this->request->get('ids'));

        return $this->success([], 204);
    }
}
