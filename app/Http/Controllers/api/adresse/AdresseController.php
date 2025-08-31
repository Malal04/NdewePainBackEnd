<?php

namespace App\Http\Controllers\Api\adresse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\adresse\AdresseService;
use App\Http\Requests\adresse\AdresseRequest;

class AdresseController extends Controller
{
    protected $adresseService;

    public function __construct(AdresseService $adresseService)
    {
        $this->adresseService = $adresseService;
    }

    public function index(Request $request)
    {
        return $this->adresseService->listAdresses($request);
    }

    public function store(AdresseRequest $request)
    {
        return $this->adresseService->createAdresse($request);
    }

    public function update(AdresseRequest $request, $id)
    {
        return $this->adresseService->updateAdresse($request, $id);
    }

    public function destroy($id)
    {
        return $this->adresseService->deleteAdresse($id);
    }

    public function choisirAdresse($id)
    {
        return $this->adresseService->choisirAdresseLivraison($id);
    }

    public function setAsPrincipale($id)
    {
        return $this->adresseService->setAsPrincipale($id);
    }

    public function getAdressePrincipale()
    {
        return $this->adresseService->getAdressePrincipale();
    }

    public function choisirRetrait()
    {
        return $this->adresseService->choisirRetrait();
    }

    public function getModeLivraisonActuel()
    {
        return $this->adresseService->getModeLivraisonActuel();
    }

    public function show($id)
    {
        return $this->adresseService->showAdresse($id);
    }

    public function listByUser($userId)
    {
        return $this->adresseService->listAdressesByUser($userId);
    }

}
