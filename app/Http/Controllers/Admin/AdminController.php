// app/Http/Controllers/Admin/AdminController.php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
class AdminController extends Controller {
    public function index() {
        return view('admin.index');
    }
}