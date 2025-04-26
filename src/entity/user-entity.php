<?php
    // namespace App\Entities;

    //use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="users")
     */
    class UserEntity {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(name="google_id", type="string", length=255, unique=true, nullable=true)
         */
        private $googleId;

        /**
         * @ORM\Column(type="string", length=255, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(type="string", length=255)
         */
        private $nombre;

        public function __construct(?int $id = null, ?string $googleId = null, string $email = '', string $nombre = '') {
            $this->id = $id;
            $this->googleId = $googleId;
            $this->email = $email;
            $this->nombre = $nombre;
        }

        public function getId(): ?int {
            return $this->id;
        }

        public function getGoogleId(): ?string {
            return $this->googleId;
        }

        public function setGoogleId(?string $googleId): self {
            $this->googleId = $googleId;
            return $this;
        }

        public function getEmail(): ?string {
            return $this->email;
        }

        public function setEmail(string $email): self {
            $this->email = $email;
            return $this;
        }

        public function getNombre(): ?string {
            return $this->nombre;
        }

        public function setNombre(string $nombre): self {
            $this->nombre = $nombre;
            return $this;
        }

    }
?>