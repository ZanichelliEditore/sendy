variable "bucket_inventory" {
  description = "Name of bucket for inventory file"
  type        = string
}

variable "environment" {
  description = "The environment of resources."
  type        = string
}
variable "instance" {
  description = "sendy instance created"
  type        = object({})
}
