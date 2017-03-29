<?php

namespace AppBundle\Entity;

use AppBundle\Intl\FranceCitiesBundle;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Utils\EmojisRemover;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;

/**
 * @ORM\Table(name="procuration_proxies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationProxyRepository")
 *
 * @UniqueEntity(fields={"emailAddress"}, message="procuration.proposal.unique")
 */
class ProcurationProxy
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The referent who invited this proxy.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $referent;

    /**
     * The associated found request.
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ProcurationRequest", inversedBy="foundProxy")
     * @ORM\JoinColumn(name="procuration_request_id", referencedColumnName="id")
     *
     * @var ProcurationRequest
     */
    private $foundRequest;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(value=0)
     * @Assert\LessThanOrEqual(value=5)
     */
    private $reliability = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *      callback={"AppBundle\ValueObject\Genders", "all"},
     *      message="common.gender.invalid_choice",
     *      strict=true
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank")
     * @Assert\Length(
     *      min=2,
     *      max=50,
     *      minMessage="procuration.last_name.min_length",
     *      maxMessage="procuration.last_name.max_length"
     * )
     */
    private $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="procuration.first_names.not_blank")
     * @Assert\Length(
     *      min=2,
     *      max=100,
     *      minMessage="procuration.first_names.min_length",
     *      maxMessage="procuration.first_names.max_length"
     * )
     */
    private $firstNames = '';

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address = '';

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15)
     */
    private $postalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Assert\Length(max=15)
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $cityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country = 'FR';

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     */
    private $emailAddress = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank")
     * @Assert\Range(max="-17 years", maxMessage="procuration.birthdate.minimum_required_age")
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15)
     */
    private $votePostalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="vote_city_insee")
     *
     * @Assert\Length(max=15)
     */
    private $voteCity;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $voteCityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $voteCountry = 'FR';

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50, groups={"vote"})
     */
    private $voteOffice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionPresidentialFirstRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionPresidentialSecondRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeFirstRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeSecondRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max=100)
     */
    private $inviteSourceName = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha = '';

    /**
     * @var bool
     *
     * @Assert\NotBlank(message="procuration.proposal_conditions.required")
     * @Assert\IsTrue(message="procuration.proposal_conditions.required")
     */
    public $conditions;

    public function __construct(Adherent $referent)
    {
        $this->referent = $referent;
        $this->phone = static::createPhoneNumber();
    }

    public function __toString()
    {
        return 'Proposition de procuration de '.$this->lastName.' '.$this->firstNames;
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null)
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    /**
     * @Assert\Callback(groups={"elections"})
     *
     * @param ExecutionContextInterface $context
     */
    public function validateElectionsChosen(ExecutionContextInterface $context)
    {
        if ($this->electionPresidentialFirstRound) {
            return;
        }

        if ($this->electionPresidentialSecondRound) {
            return;
        }

        if ($this->electionLegislativeFirstRound) {
            return;
        }

        if ($this->electionLegislativeSecondRound) {
            return;
        }

        $context->addViolation('Vous devez choisir au moins une élection');
    }

    public function importAdherentData(Adherent $adherent)
    {
        $this->gender = $adherent->getGender();
        $this->setFirstNames($adherent->getFirstName());
        $this->setLastName($adherent->getLastName());
        $this->emailAddress = $adherent->getEmailAddress();
        $this->setAddress($adherent->getAddress());
        $this->postalCode = $adherent->getPostalCode();
        $this->city = $adherent->getCity();
        $this->setCityName($adherent->getCityName());
        $this->country = $adherent->getCountry();

        if ($adherent->getPhone()) {
            $this->phone = $adherent->getPhone();
        }

        if ($adherent->getBirthdate()) {
            $this->birthdate = $adherent->getBirthdate();
        }
    }

    public function getRemainingAvailabilities(): array
    {
        $availabilities = [
            'presidential' => [
                'first' => $this->getElectionPresidentialFirstRound(),
                'second' => $this->getElectionPresidentialSecondRound(),
            ],
            'legislatives' => [
                'first' => $this->getElectionLegislativeFirstRound(),
                'second' => $this->getElectionLegislativeSecondRound(),
            ],
        ];

        $request = $this->getFoundRequest();

        if (!$request) {
            return $availabilities;
        }

        if ($request->getElectionPresidentialFirstRound()) {
            $availabilities['presidential']['first'] = false;
        }

        if ($request->getElectionPresidentialSecondRound()) {
            $availabilities['presidential']['second'] = false;
        }

        if ($request->getElectionLegislativeFirstRound()) {
            $availabilities['legislatives']['first'] = false;
        }

        if ($request->getElectionLegislativeSecondRound()) {
            $availabilities['legislatives']['second'] = false;
        }

        return $availabilities;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getReferent(): ?Adherent
    {
        return $this->referent;
    }

    public function setReferent(Adherent $referent = null)
    {
        $this->referent = $referent;
    }

    public function getReliability(): ?int
    {
        return $this->reliability;
    }

    public function setReliability(?int $reliability)
    {
        $this->reliability = $reliability;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName)
    {
        $this->lastName = EmojisRemover::remove($lastName);
    }

    public function getFirstNames(): ?string
    {
        return $this->firstNames;
    }

    public function setFirstNames(?string $firstNames)
    {
        $this->firstNames = EmojisRemover::remove($firstNames);
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address)
    {
        $this->address = EmojisRemover::remove($address);
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity(?string $cityCode)
    {
        $this->city = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->cityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getCityName()
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName)
    {
        if ($cityName) {
            $this->cityName = EmojisRemover::remove($cityName);
        }
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePostalCode(): ?string
    {
        return $this->votePostalCode;
    }

    public function setVotePostalCode(?string $votePostalCode)
    {
        $this->votePostalCode = $votePostalCode;
    }

    public function getVoteCity()
    {
        return $this->voteCity;
    }

    public function setVoteCity(?string $cityCode)
    {
        $this->voteCity = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->voteCityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getVoteCityName()
    {
        return $this->voteCityName;
    }

    public function setVoteCityName(?string $voteCityName)
    {
        if ($voteCityName) {
            $this->voteCityName = EmojisRemover::remove($voteCityName);
        }
    }

    public function getVoteCountry(): ?string
    {
        return $this->voteCountry;
    }

    public function setVoteCountry(?string $voteCountry)
    {
        $this->voteCountry = $voteCountry;
    }

    public function getVoteOffice(): ?string
    {
        return $this->voteOffice;
    }

    public function setVoteOffice(?string $voteOffice)
    {
        $this->voteOffice = $voteOffice;
    }

    public function getElectionPresidentialFirstRound(): bool
    {
        return $this->electionPresidentialFirstRound;
    }

    public function setElectionPresidentialFirstRound(bool $electionPresidentialFirstRound)
    {
        $this->electionPresidentialFirstRound = $electionPresidentialFirstRound;
    }

    public function getElectionPresidentialSecondRound(): bool
    {
        return $this->electionPresidentialSecondRound;
    }

    public function setElectionPresidentialSecondRound(bool $electionPresidentialSecondRound)
    {
        $this->electionPresidentialSecondRound = $electionPresidentialSecondRound;
    }

    public function getElectionLegislativeFirstRound(): bool
    {
        return $this->electionLegislativeFirstRound;
    }

    public function setElectionLegislativeFirstRound(bool $electionLegislativeFirstRound)
    {
        $this->electionLegislativeFirstRound = $electionLegislativeFirstRound;
    }

    public function getElectionLegislativeSecondRound(): bool
    {
        return $this->electionLegislativeSecondRound;
    }

    public function setElectionLegislativeSecondRound(bool $electionLegislativeSecondRound)
    {
        $this->electionLegislativeSecondRound = $electionLegislativeSecondRound;
    }

    public function getFoundRequest(): ?ProcurationRequest
    {
        return $this->foundRequest;
    }

    public function setFoundRequest(ProcurationRequest $foundRequest = null)
    {
        $this->foundRequest = $foundRequest;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled)
    {
        $this->disabled = $disabled;
    }

    public function getInviteSourceName(): ?string
    {
        return $this->inviteSourceName;
    }

    public function setInviteSourceName(string $inviteSourceName = null)
    {
        $this->inviteSourceName = $inviteSourceName;
    }
}