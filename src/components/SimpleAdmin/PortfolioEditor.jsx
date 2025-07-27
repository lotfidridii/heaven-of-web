import React, { useState, useRef } from 'react';
import { Form, Button, Row, Col, Card, Modal, Table } from 'react-bootstrap';
import { motion } from 'framer-motion';
import { useSiteData } from '../../contexts/SiteDataContext';

const PortfolioEditor = ({ onNotification }) => {
  const { data, addPortfolioItem, removePortfolioItem, updatePortfolio } = useSiteData();
  const [showModal, setShowModal] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    alt: '',
    src: '',
    link: '',
    linkText: ''
  });
  const [imagePreview, setImagePreview] = useState(null);
  const [isDragOver, setIsDragOver] = useState(false);
  const [uploadStatus, setUploadStatus] = useState(null); // 'uploading', 'success', 'error'
  const [uploadMessage, setUploadMessage] = useState('');
  const fileInputRef = useRef(null);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleAddNew = () => {
    setEditingItem(null);
    setFormData({
      title: '',
      alt: '',
      src: '',
      link: '',
      linkText: ''
    });
    setImagePreview(null);
    setUploadStatus(null);
    setUploadMessage('');
    setShowModal(true);
  };

  // Image upload functions
  const handleImageUpload = async (file) => {
    if (!file || !file.type.startsWith('image/')) {
      onNotification('Please select a valid image file', 'danger');
      return;
    }

    if (file.size > 10 * 1024 * 1024) { // 10MB limit
      onNotification('File size must be less than 10MB', 'danger');
      return;
    }

    setUploadStatus('uploading');
    setUploadMessage('Uploading image...');

    try {
      // In development, simulate upload without PHP server
      if (process.env.NODE_ENV !== 'production') {
        // Development mode: simulate upload and use blob URL
        const fileName = `Portfolio-${Date.now()}.${file.name.split('.').pop()}`;
        const simulatedPath = `./img/${fileName}`;
        
        setFormData(prev => ({
          ...prev,
          src: simulatedPath
        }));
        setImagePreview(URL.createObjectURL(file));
        setUploadStatus('success');
        setUploadMessage('Image uploaded successfully! (Development mode)');
        onNotification('Image uploaded successfully! (Development mode)', 'success');
        return;
      }

      // Production mode: use PHP upload
      const formData = new FormData();
      formData.append('image', file);

      const response = await fetch('/api/upload.php', {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        setFormData(prev => ({
          ...prev,
          src: result.path
        }));
        setImagePreview(URL.createObjectURL(file));
        setUploadStatus('success');
        setUploadMessage('Image uploaded successfully!');
        onNotification('Image uploaded successfully!', 'success');
      } else {
        throw new Error(result.message || 'Upload failed');
      }
    } catch (error) {
      setUploadStatus('error');
      setUploadMessage('Upload failed. Please try again.');
      onNotification('Upload failed: ' + error.message, 'danger');
    }
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    setIsDragOver(true);
  };

  const handleDragLeave = (e) => {
    e.preventDefault();
    setIsDragOver(false);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setIsDragOver(false);
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      handleImageUpload(files[0]);
    }
  };

  const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
      handleImageUpload(file);
    }
  };

  const handleEdit = (item) => {
    setEditingItem(item);
    setFormData({
      title: item.title,
      alt: item.alt,
      src: item.src,
      link: item.link,
      linkText: item.linkText
    });
    // Set image preview if editing existing item with image
    if (item.src && !item.src.startsWith('./img/')) {
      setImagePreview(item.src);
    } else {
      setImagePreview(null);
    }
    setUploadStatus(null);
    setUploadMessage('');
    setShowModal(true);
  };

  const handleSave = async () => {
    if (!formData.title || !formData.alt || !formData.src) {
      onNotification('Please fill in all required fields', 'danger');
      return;
    }

    try {
      if (editingItem) {
        // Update existing item
        const updatedPortfolio = data.portfolio.map(item => 
          item.id === editingItem.id 
            ? { ...item, ...formData }
            : item
        );
        await updatePortfolio(updatedPortfolio);
        onNotification('Portfolio item updated successfully!', 'success');
        setShowModal(false);
      } else {
        // Add new item
        await addPortfolioItem(formData);
        onNotification('Portfolio item added successfully!', 'success');
        setShowModal(false);
      }
    } catch (error) {
      console.error('Error saving portfolio item:', error);
      onNotification('Error saving portfolio item: ' + error.message, 'danger');
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this portfolio item?')) {
      try {
        await removePortfolioItem(id);
        onNotification('Portfolio item deleted successfully!', 'success');
      } catch (error) {
        console.error('Error deleting portfolio item:', error);
        onNotification('Error deleting portfolio item: ' + error.message, 'danger');
      }
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.3 }}
    >
      <Card>
        <Card.Header className="d-flex justify-content-between align-items-center">
          <h5>Portfolio Management</h5>
          <Button variant="primary" onClick={handleAddNew}>
            Add New Item
          </Button>
        </Card.Header>
        <Card.Body>
          <div className="table-responsive">
            <Table striped bordered hover>
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Alt Text</th>
                  <th>Image</th>
                  <th>Link</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {data.portfolio.map((item) => (
                  <tr key={item.id}>
                    <td>{item.title}</td>
                    <td>{item.alt}</td>
                    <td>
                      <small className="text-muted">{item.src}</small>
                    </td>
                    <td>
                      {item.link && (
                        <a href={item.link} target="_blank" rel="noopener noreferrer">
                          {item.linkText || 'View'}
                        </a>
                      )}
                    </td>
                    <td>
                      <div className="d-flex gap-2 justify-content-center align-items-center">
                        {/* Edit Button */}
                        <Button 
                          size="sm" 
                          variant="primary"
                          onClick={() => handleEdit(item)}
                          title="Edit Portfolio Item"
                          className="d-flex align-items-center gap-1"
                          style={{ minWidth: '60px' }}
                        >
                          ✏️ Edit
                        </Button>
                        
                        {/* Delete Button */}
                        <Button 
                          size="sm" 
                          variant="danger"
                          onClick={() => handleDelete(item.id)}
                          title="Delete Portfolio Item"
                          className="d-flex align-items-center gap-1"
                          style={{ minWidth: '70px' }}
                        >
                          🗑️ Delete
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>

          {data.portfolio.length === 0 && (
            <div className="text-center py-4">
              <p className="text-muted">No portfolio items yet. Add your first item!</p>
            </div>
          )}
        </Card.Body>
      </Card>

      {/* Add/Edit Modal */}
      <Modal show={showModal} onHide={() => setShowModal(false)} size="lg">
        <Modal.Header closeButton>
          <Modal.Title>
            {editingItem ? 'Edit Portfolio Item' : 'Add New Portfolio Item'}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <Form>
            <Row>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Title *</Form.Label>
                  <Form.Control
                    type="text"
                    value={formData.title}
                    onChange={(e) => handleInputChange('title', e.target.value)}
                    placeholder="e.g., Conception de site web"
                  />
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>Alt Text *</Form.Label>
                  <Form.Control
                    type="text"
                    value={formData.alt}
                    onChange={(e) => handleInputChange('alt', e.target.value)}
                    placeholder="e.g., Company Name"
                  />
                </Form.Group>
              </Col>
            </Row>

            <Form.Group className="mb-3">
              <Form.Label>Portfolio Image *</Form.Label>
              <div
                className={`image-upload-area ${isDragOver ? 'dragover' : ''}`}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                onClick={() => fileInputRef.current?.click()}
              >
                {imagePreview ? (
                  <div>
                    <img src={imagePreview} alt="Preview" className="image-preview" />
                    <div className="upload-text">
                      <strong>Click to change image</strong> or drag and drop a new one
                    </div>
                  </div>
                ) : (
                  <div className="upload-text">
                    <div style={{ fontSize: '2rem', marginBottom: '1rem' }}>🖼️</div>
                    <div><strong>Click to upload</strong> or drag and drop</div>
                    <div>PNG, JPG, WEBP files supported</div>
                    <small style={{ opacity: 0.8, marginTop: '0.5rem', display: 'block' }}>Images will be saved to public/img/ folder</small>
                  </div>
                )}
              </div>
              <input
                type="file"
                ref={fileInputRef}
                onChange={handleFileSelect}
                accept="image/*"
                style={{ display: 'none' }}
              />
              <Form.Text className="text-muted">
                Upload an image for your portfolio item
              </Form.Text>
              {uploadStatus && (
                <div className={`upload-status ${uploadStatus}`}>
                  {uploadStatus === 'uploading' && '⏳ '}
                  {uploadStatus === 'success' && '✅ '}
                  {uploadStatus === 'error' && '❌ '}
                  {uploadMessage}
                </div>
              )}
            </Form.Group>

            <Row>
              <Col md={8}>
                <Form.Group className="mb-3">
                  <Form.Label>Website Link</Form.Label>
                  <Form.Control
                    type="url"
                    value={formData.link}
                    onChange={(e) => handleInputChange('link', e.target.value)}
                    placeholder="https://example.com"
                  />
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group className="mb-3">
                  <Form.Label>Link Text</Form.Label>
                  <Form.Control
                    type="text"
                    value={formData.linkText}
                    onChange={(e) => handleInputChange('linkText', e.target.value)}
                    placeholder="voir le site web"
                  />
                </Form.Group>
              </Col>
            </Row>
          </Form>
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowModal(false)}>
            Cancel
          </Button>
          <Button variant="primary" onClick={handleSave}>
            {editingItem ? 'Update' : 'Add'} Item
          </Button>
        </Modal.Footer>
      </Modal>
    </motion.div>
  );
};

export default PortfolioEditor;
