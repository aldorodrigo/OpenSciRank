<?php

namespace Database\Factories;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Journal>
 */
class JournalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Journal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $journals = self::getRealJournals();
        
        // Use static counter to go through the journal list sequentially if called multiple times,
        // or just pick a unique random element. We'll use unique()->randomElement to avoid duplicates.
        $journal = $this->faker->unique()->randomElement($journals);

        return [
            'user_id' => User::factory(),
            'title' => $journal['title'],
            'slug' => Str::slug($journal['title']),
            'issn_print' => $this->faker->numerify('####-####'),
            'issn_online' => $this->faker->numerify('####-####'),
            'publisher' => $journal['publisher'],
            'url' => 'https://www.' . Str::slug($journal['publisher']) . '.com',
            'country_code' => $journal['country_code'],
            'status' => 'indexed',
            'current_score' => $this->faker->randomFloat(2, 1, 100),
            'current_level' => $this->faker->randomElement(['Q1', 'Q2', 'Q3', 'Q4']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Returns a list of 50 real indexed journals across 20 countries.
     * Countries: US, GB, NL, CH, DE, IT, FR, ES, PT, CL, AR, BR, MX, CO, PE, CU, VE, UY, CR, EC.
     *
     * @return array
     */
    public static function getRealJournals(): array
    {
        return [
            // Chile (CL)
            ['title' => 'Revista Médica de Chile', 'country_code' => 'CL', 'publisher' => 'Sociedad Médica de Santiago'],
            ['title' => 'Cinta de Moebio', 'country_code' => 'CL', 'publisher' => 'Universidad de Chile'],
            ['title' => 'Revista Chilena de Infectología', 'country_code' => 'CL', 'publisher' => 'Sociedad Chilena de Infectología'],
            ['title' => 'Chungara, Revista de Antropología Chilena', 'country_code' => 'CL', 'publisher' => 'Universidad de Tarapacá'],
            
            // Argentina (AR)
            ['title' => 'Medicina (Buenos Aires)', 'country_code' => 'AR', 'publisher' => 'Fundación Revista Medicina'],
            ['title' => 'Salud Colectiva', 'country_code' => 'AR', 'publisher' => 'Universidad Nacional de Lanús'],
            
            // Brazil (BR)
            ['title' => 'Memórias do Instituto Oswaldo Cruz', 'country_code' => 'BR', 'publisher' => 'Instituto Oswaldo Cruz'],
            ['title' => 'Revista de Saúde Pública', 'country_code' => 'BR', 'publisher' => 'Universidade de São Paulo'],
            ['title' => 'Anais da Academia Brasileira de Ciências', 'country_code' => 'BR', 'publisher' => 'Academia Brasileira de Ciências'],
            ['title' => 'Cadernos de Saúde Pública', 'country_code' => 'BR', 'publisher' => 'Escola Nacional de Saúde Pública'],
            
            // Mexico (MX)
            ['title' => 'Salud Pública de México', 'country_code' => 'MX', 'publisher' => 'Instituto Nacional de Salud Pública'],
            ['title' => 'Revista Mexicana de Ciencias Políticas y Sociales', 'country_code' => 'MX', 'publisher' => 'UNAM'],
            ['title' => 'Archives of Medical Research', 'country_code' => 'MX', 'publisher' => 'IMSS'],
            
            // Colombia (CO)
            ['title' => 'Revista Colombiana de Psiquiatría', 'country_code' => 'CO', 'publisher' => 'Asociación Colombiana de Psiquiatría'],
            ['title' => 'Biomédica', 'country_code' => 'CO', 'publisher' => 'Instituto Nacional de Salud'],
            
            // Peru (PE)
            ['title' => 'Revista Peruana de Medicina Experimental y Salud Pública', 'country_code' => 'PE', 'publisher' => 'Instituto Nacional de Salud'],
            ['title' => 'Revista de Investigaciones Veterinarias del Perú', 'country_code' => 'PE', 'publisher' => 'Universidad Nacional Mayor de San Marcos'],
            
            // Cuba (CU)
            ['title' => 'Revista Cubana de Medicina Tropical', 'country_code' => 'CU', 'publisher' => 'Instituto de Medicina Tropical Pedro Kourí'],
            ['title' => 'Revista Cubana de Salud Pública', 'country_code' => 'CU', 'publisher' => 'Sociedad Cubana de Salud Pública'],
            ['title' => 'Biotecnología Aplicada', 'country_code' => 'CU', 'publisher' => 'Elfos Scientiae'],
            
            // Venezuela (VE)
            ['title' => 'Investigación Clínica', 'country_code' => 'VE', 'publisher' => 'Universidad del Zulia'],
            ['title' => 'Archivos Venezolanos de Farmacología y Terapéutica', 'country_code' => 'VE', 'publisher' => 'Sociedad Venezolana de Farmacología'],
            
            // Uruguay (UY)
            ['title' => 'Revista Médica del Uruguay', 'country_code' => 'UY', 'publisher' => 'Sindicato Médico del Uruguay'],
            ['title' => 'Agrociencia Uruguay', 'country_code' => 'UY', 'publisher' => 'Universidad de la República'],
            
            // Costa Rica (CR)
            ['title' => 'Revista de Biología Tropical', 'country_code' => 'CR', 'publisher' => 'Universidad de Costa Rica'],
            ['title' => 'Población y Salud en Mesoamérica', 'country_code' => 'CR', 'publisher' => 'Universidad de Costa Rica'],
            
            // Ecuador (EC)
            ['title' => 'Revista Ecuatoriana de Neurología', 'country_code' => 'EC', 'publisher' => 'Sociedad Ecuatoriana de Neurología'],
            ['title' => 'Enfoque UTE', 'country_code' => 'EC', 'publisher' => 'Universidad Tecnológica Equinoccial'],
            
            // Spain (ES)
            ['title' => 'Revista Española de Cardiología', 'country_code' => 'ES', 'publisher' => 'Sociedad Española de Cardiología'],
            ['title' => 'Gaceta Sanitaria', 'country_code' => 'ES', 'publisher' => 'Sociedad Española de Salud Pública'],
            ['title' => 'El Profesional de la Información', 'country_code' => 'ES', 'publisher' => 'EPI CSP'],
            ['title' => 'Revista iberoamericana de educación', 'country_code' => 'ES', 'publisher' => 'OEI'],
            ['title' => 'Revista Clínica Española', 'country_code' => 'ES', 'publisher' => 'Sociedad Española de Medicina Interna'],
            
            // Portugal (PT)
            ['title' => 'Acta Médica Portuguesa', 'country_code' => 'PT', 'publisher' => 'Ordem dos Médicos'],
            ['title' => 'Revista Portuguesa de Pneumologia', 'country_code' => 'PT', 'publisher' => 'Sociedade Portuguesa de Pneumologia'],
            
            // United States (US)
            ['title' => 'New England Journal of Medicine', 'country_code' => 'US', 'publisher' => 'Massachusetts Medical Society'],
            ['title' => 'Science', 'country_code' => 'US', 'publisher' => 'AAAS'],
            ['title' => 'PLOS One', 'country_code' => 'US', 'publisher' => 'Public Library of Science'],
            ['title' => 'Cell', 'country_code' => 'US', 'publisher' => 'Cell Press'],
            
            // United Kingdom (GB)
            ['title' => 'The Lancet', 'country_code' => 'GB', 'publisher' => 'Elsevier'],
            ['title' => 'Nature', 'country_code' => 'GB', 'publisher' => 'Nature Publishing Group'],
            ['title' => 'BMJ Open', 'country_code' => 'GB', 'publisher' => 'BMJ Publishing Group'],
            
            // Netherlands (NL)
            ['title' => 'Journal of Informetrics', 'country_code' => 'NL', 'publisher' => 'Elsevier'],
            ['title' => 'Scientometrics', 'country_code' => 'NL', 'publisher' => 'Springer'],
            
            // Switzerland (CH)
            ['title' => 'Sensors', 'country_code' => 'CH', 'publisher' => 'MDPI'],
            ['title' => 'Molecules', 'country_code' => 'CH', 'publisher' => 'MDPI'],
            ['title' => 'International Journal of Public Health', 'country_code' => 'CH', 'publisher' => 'Springer'],
            
            // Germany (DE)
            ['title' => 'Angewandte Chemie', 'country_code' => 'DE', 'publisher' => 'Wiley-VCH'],
            
            // Italy (IT)
            ['title' => 'Minerva Medica', 'country_code' => 'IT', 'publisher' => 'Edizioni Minerva Medica'],
            
            // France (FR)
            ['title' => 'Comptes Rendus Biologies', 'country_code' => 'FR', 'publisher' => 'Académie des sciences'],
        ];
    }
}
