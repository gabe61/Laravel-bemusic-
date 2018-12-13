<?php namespace Common\Core;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Common\Core\BaseFormRequest;
use Common\Auth\Roles\Role;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Authorize a given action for the current user
     * or guest if user is not logged in.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        if (Auth::check()) {
            list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);
            return app(Gate::class)->authorize($ability, $arguments);
        } else {
            $guest = new User();
            $guest->setRelation('roles', Role::where('guests', 1)->get());
            return $this->authorizeForUser($guest, $ability, $arguments);
        }
    }

    public function success($items = null, $status = 200)
    {
        $data = ['status' => 'success'];

        if ($items instanceof Arrayable) {
            $items = $items->toArray();
        }

        if ($items) {
            foreach($items as $key => $item) {
                $data[$key] = $item;
            }
        }

        return response()->json($data, $status);
    }

    /**
     * Return error response with specified messages.
     *
     * @param array $items
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($items = null, $status = 422)
    {
        $data = ['status' => 'error', 'messages' => []];

        if ($items) {
            foreach($items as $key => $item) {
                $data['messages'][$key] = $item;
            }
        }

        return response()->json($data, $status);
    }

    /**
     * Format the validation errors to be returned.
     *
     * @param  Validator  $validator
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        $response = BaseFormRequest::formatValidationErrors($validator);

        return $response;
    }
}
