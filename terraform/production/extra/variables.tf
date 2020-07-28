variable "instance_public_ip" {
  description = "The IP address of instance."
  type        = string
}
variable "environment" {
  description = "The enviroment of resources."
  type        = string
}
variable "project" {
  description = "The project of resources."
  type        = string
}
variable "bucket_inventory" {
  description = "Name of bucket for inventory file"
  type        = string
}
variable "port" {
  description = "Port for inventory file"
  type        = string
  default     = "30022"
}
