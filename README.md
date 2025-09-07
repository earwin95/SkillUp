# SkillUp – Plateforme d’échange de compétences

SkillUp est une plateforme web qui permet aux utilisateurs de proposer leurs compétences et de rechercher celles des autres, dans un esprit de **troc** (ex : « 2h de guitare contre 2h de développement web »).  
Le projet met en avant la **collaboration** et le **partage de savoir-faire**, sans échange monétaire.

---

## 🚀 Technologies

- [Symfony 7](https://symfony.com/) – framework PHP
- [Doctrine ORM](https://www.doctrine-project.org/) – gestion de la base de données
- [Twig](https://twig.symfony.com/) – moteur de templates
- [Tailwind CSS](https://tailwindcss.com/) – design moderne et responsive
- [Symfony Security](https://symfony.com/doc/current/security.html) – gestion de l’authentification & rôles
- MySQL ou SQLite en développement

---

## ✨ Fonctionnalités

- **Gestion des utilisateurs**
  - Inscription / connexion / déconnexion
  - Profil utilisateur avec bio et compétences
  - Rôles (user / admin)

- **Compétences (Skills)**
  - Ajout, description et niveau
  - Association avec l’utilisateur

- **Offres (Offers)**
  - Création / édition / suppression
  - Recherche et filtrage par compétence offerte, demandée ou mot-clé
  - Statut d’offre (active, close…)

- **Demandes d’échange (ExchangeRequest)**
  - Création d’une demande sur une offre
  - Acceptation / refus par le propriétaire de l’offre
  - Suivi du statut (PENDING, ACCEPTED, REFUSED)

- **Conversations & Messages**
  - Discussions entre participants autour d’une offre
  - Historique de messages

- **Avis (Reviews)**
  - Notation et commentaire après un échange
  - Mise en avant des utilisateurs fiables

---

## 🗄️ Entités principales

- **User** : informations de connexion + profil
- **Skill** : compétences disponibles
- **Offer** : une offre créée par un utilisateur (avec skill offerte et demandée)
- **ExchangeRequest** : demande d’échange liée à une offre
- **Conversation** : discussion entre deux utilisateurs autour d’une offre
- **Message** : messages associés à une conversation
- **Review** : avis laissés après un échange

---