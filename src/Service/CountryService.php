<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CountryService
{
    private HttpClientInterface $client;
    private $africanCountries = [
        "Afrique du Sud",
        "Algérie",
        "Angola",
        "Bénin",
        "Botswana",
        "Burkina Faso",
        "Burundi",
        "Cap-Vert",
        "Cameroun",
        "Comores",
        "Congo (Brazzaville)",
        "Congo (Kinshasa) - République démocratique du Congo",
        "Côte d'Ivoire",
        "Djibouti",
        "Égypte",
        "Eswatini (Swaziland)",
        "Éthiopie",
        "Gabon",
        "Gambie",
        "Ghana",
        "Guinée",
        "Guinée-Bissau",
        "Guinée équatoriale",
        "Kenya",
        "Lesotho",
        "Liberia",
        "Libye",
        "Madagascar",
        "Malawi",
        "Mali",
        "Maroc",
        "Maurice",
        "Mauritanie",
        "Mozambique",
        "Namibie",
        "Niger",
        "Nigeria",
        "Ouganda",
        "République Centrafricaine",
        "Rwanda",
        "Sao Tomé-et-Principe",
        "Sénégal",
        "Seychelles",
        "Sierra Leone",
        "Somalie",
        "Soudan",
        "Soudan du Sud",
        "Tanzanie",
        "Tchad",
        "Togo",
        "Tunisie",
        "Zambie",
        "Zimbabwe"
    ];

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Formate un numéro de téléphone pour inclure le code téléphonique du pays.
     *
     * @param string $country Nom du pays.
     * @param string $phoneNumber Numéro de téléphone à formater.
     *
     * @return string Numéro de téléphone avec le code téléphonique du pays.
     * @throws \Exception Si le pays ou les données ne peuvent être récupérés.
     */
    public function formatPhoneNumberWithCountryCode(string $countryId, string $phoneNumber): string
    {
        $country = $this->africanCountries[$countryId];
        // Remplacer {name} par le nom du pays dans l'URL
        $response = $this->client->request('GET', 'https://restcountries.com/v3.1/name/' . urlencode($country), [
            'http_version' => '1.1',
            'timeout' => 20,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des données pour le pays ' . $country);
        }

        $data = $response->toArray();

        // Vérifier si des données existent pour le pays
        if (empty($data) || !isset($data[0]['idd']['root'])) {
            throw new \Exception('Données indisponibles pour le pays ' . $country);
        }

        // Récupérer le code téléphonique
        $phoneCode = $data[0]['idd']['root'] ?? '';
        $suffixes = $data[0]['idd']['suffixes'] ?? [''];

        // Utiliser le premier suffixe disponible
        $fullCode = $phoneCode . ($suffixes[0] ?? '');

        // Extraire les 9 derniers chiffres du numéro de téléphone
        $lastNineDigits = substr(preg_replace('/\D/', '', $phoneNumber), -9);

        // Construire le numéro complet
        $formattedNumber = $fullCode . $lastNineDigits;

        return $formattedNumber;
    }

    public function getAfricanCountry(int $id): string{
        // Récupérer le nom du pays à partir de son ID dans le tablau
        return $this->africanCountries[$id]?? '';

    }
}
