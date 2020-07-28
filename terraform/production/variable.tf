variable "profile" {
  description = "Profile defined in .aws/credentials authorized to use ced production."
  type        = string
  default     = "zanichelli-ced-terraform"
}
variable "region" {
  description = "Region on which to execute commands."
  type        = string
  default     = "eu-west-1"
}
variable "public_key_file" {
  description = "public key file of bastion host"
  default     = "bastion.key.pub"
  type        = string
}
variable "environment" {
  description = "The enviroment of resources."
  type        = string
  default     = "production"
}